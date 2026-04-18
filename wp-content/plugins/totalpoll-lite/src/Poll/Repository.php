<?php

namespace TotalPoll\Poll;

use TotalPoll\Contracts\Poll\Model as ModelContract;
use TotalPoll\Contracts\Poll\Repository as RepositoryContract;
use TotalPollVendors\TotalCore\Application;
use TotalPollVendors\TotalCore\Helpers\Arrays;
use TotalPollVendors\TotalCore\Helpers\Sql;
use TotalPollVendors\TotalCore\Http\Request;
use WP_Query;

/**
 * Poll repository.
 *
 * @package TotalPoll\Poll
 * @since   4.0.0
 */
class Repository implements RepositoryContract
{
    /**
     * @var Request $request
     */
    protected $request;
    /**
     * @var \wpdb $database
     */
    protected $database;
    /**
     * @var array $env
     */
    protected $env;

    private $randomPolls = [];

    /**
     * Repository constructor.
     *
     * @param  Request  $request
     * @param  \wpdb  $database
     * @param  array  $env
     */
    public function __construct(Request $request, \wpdb $database, $env)
    {
        $this->request = $request;
        $this->database = $database;
        $this->env = $env;
    }

    /**
     * Get polls.
     *
     * @param $query
     *
     * @return ModelContract[]
     */
    public function get($query)
    {
        $args = Arrays::parse(
            $query,
            [
                'page'           => 1,
                'perPage'        => 10,
                'orderBy'        => 'date',
                'orderDirection' => 'DESC',
                'status'         => 'publish',
                'wpQuery'        => [],
            ]
        );

        // Models
        $pollModels = [];
        // Query
        $wpQueryArgs = Arrays::parse(
            [
                'post_type'      => TP_POLL_CPT_NAME,
                'post_status'    => $args['status'],
                'paged'          => $args['page'],
                'posts_per_page' => $args['perPage'],
                'order'          => $args['orderDirection'],
                'orderby'        => $args['orderBy'],
            ],
            $args['wpQuery']
        );

        /**
         * Filters the list of arguments used for get polls query.
         *
         * @param  array  $wpQueryArgs  WP_Query arguments.
         * @param  array  $args  Arguments.
         * @param  array  $query  Query.
         *
         * @return array
         * @since 4.0.0
         */
        $wpQueryArgs = apply_filters('totalpoll/filters/polls/get/query', $wpQueryArgs, $args, $query);

        $wpQuery = new WP_Query($wpQueryArgs);

        // Iterate and convert each row to log model
        foreach ($wpQuery->get_posts() as $pollPost):
            $pollModels[] = $this->getById($pollPost);
        endforeach;

        /**
         * Filters the results of get polls query.
         *
         * @param  ModelContract[]  $pollModels  Polls models.
         * @param  array  $wpQueryArgs  WP_Query arguments.
         * @param  array  $args  Arguments.
         * @param  array  $query  Query.
         *
         * @return array
         * @since 4.0.0
         */
        $pollModels = apply_filters('totalpoll/filters/polls/get/results', $pollModels, $wpQueryArgs, $args, $query);

        // Return models
        return $pollModels;
    }

    /**
     * Get poll.
     *
     * @param $pollId
     *
     * @return null|ModelContract
     * @since 4.0.0
     */
    public function getById($pollId)
    {
        $attributes = [];
        // Post
        if ($pollId instanceof \WP_Post):
            $attributes['post'] = $pollId;
        else:
            $attributes['post'] = get_post($pollId);
            if (!$attributes['post']):
                return null;
            endif;
        endif;

        $attributes['id'] = $attributes['post']->ID;
        $attributes['action'] = $this->request->request('totalpoll.action');
        $attributes['ip'] = $this->request->ip();
        $attributes['currentPage'] = (int)get_query_var('current_page', (int)get_query_var('paged', 1));
        if (empty($attributes['currentPage'])):
            $attributes['currentPage'] = $this->request->request('totalpoll.page', 1);
        endif;

        $container = TotalPoll()->container();

        if (!$container->has("poll.instances.{$attributes['id']}")):
            $attributes['votes'] = $this->getVotes($attributes['id']);

            /**
             * Filters the poll model attributes after retrieving.
             *
             * @param  array  $attributes  Entry attributes.
             *
             * @return array
             * @since 4.0.0
             */
            $attributes = apply_filters('totalpoll/filters/polls/get/attributes', $attributes);
            $pollModel = new Model($attributes);

            /**
             * Filters the poll model after creation and before adding it to container.
             *
             * @param  ModelContract  $model  Poll model object.
             * @param  array  $attributes  Poll attributes.
             *
             * @return array
             * @since 4.0.0
             */
            $pollModel = apply_filters('totalpoll/filters/polls/get/model', $pollModel, $attributes);

            $container->share("poll.instances.{$attributes['id']}", $pollModel);
        endif;

        return $container->get("poll.instances.{$attributes['id']}");
    }

    /**
     * Get poll.
     *
     * @return null|ModelContract
     * @since 4.0.0
     */
    public function getRandomly()
    {
        $attributes = [];

        $posts = get_posts(
            [
                'post_type'   => TP_POLL_CPT_NAME,
                'orderby'     => 'rand',
                'post_status' => 'publish',
                'numberposts' => 1,
                'exclude'     => $this->randomPolls
            ]
        );

        if (empty($posts)):
            return null;
        endif;

        $attributes['post'] = current($posts);

        $attributes['id'] = $attributes['post']->ID;
        $attributes['action'] = $this->request->request('totalpoll.action');
        $attributes['ip'] = $this->request->ip();
        $attributes['currentPage'] = (int)get_query_var('current_page', (int)get_query_var('paged', 1));
        if (empty($attributes['currentPage'])):
            $attributes['currentPage'] = $this->request->request('totalpoll.page', 1);
        endif;

        $container = TotalPoll()->container();

        if (!$container->has("poll.instances.{$attributes['id']}")):
            $attributes['votes'] = $this->getVotes($attributes['id']);

            /**
             * Filters the poll model attributes after retrieving.
             *
             * @param  array  $attributes  Entry attributes.
             *
             * @return array
             * @since 4.0.0
             */
            $attributes = apply_filters('totalpoll/filters/polls/get/attributes', $attributes);
            $pollModel = new Model($attributes);

            /**
             * Filters the poll model after creation and before adding it to container.
             *
             * @param  ModelContract  $model  Poll model object.
             * @param  array  $attributes  Poll attributes.
             *
             * @return array
             * @since 4.0.0
             */
            $pollModel = apply_filters('totalpoll/filters/polls/get/model', $pollModel, $attributes);

            $container->share("poll.instances.{$attributes['id']}", $pollModel);
        endif;

        $this->randomPolls[] = $attributes['id'];

        return $container->get("poll.instances.{$attributes['id']}");
    }

    /**
     * Get choice(s) votes.
     *
     * @param  int  $pollId
     * @param  string|null  $choiceUid
     *
     * @return mixed
     * @since 4.0.0
     */
    public function getVotes($pollId, $choiceUid = null)
    {
        $votes = [];

        // Where clause
        $where = ['poll_id' => (int)$pollId];
        if (!empty($choiceUid)):
            $where['choice_uid'] = [$choiceUid];
        endif;

        /**
         * Filters the list of where clauses used in votes counting query.
         *
         * @param  array  $args  Arguments.
         * @param  array  $query  Query.
         *
         * @return array
         * @since 4.0.0
         */
        $where = apply_filters('totalpoll/filters/polls/votes/query', $where);
        $where = Sql::generateWhereClause($where);

        // Query
        $query = "SELECT choice_uid, votes FROM `{$this->env['db.tables.votes']}` {$where}";

        // Results
        $rows = $this->database->get_results($query, ARRAY_A);
        foreach ($rows as $row):
            $votes[$row['choice_uid']] = (int)$row['votes'];
        endforeach;

        /**
         * Filters the results of votes counting query.
         *
         * @param  array  $votes  Array of choices [choiceUid => votes].
         * @param  array  $args  Arguments.
         * @param  array  $query  Query.
         *
         * @return array
         * @since 4.0.0
         */
        $votes = apply_filters('totalpoll/filters/polls/votes/results', $votes);

        return $votes;
    }

    /**
     * Increment choice(s) votes.
     *
     * @param  int  $pollId
     * @param  array  $choices
     *
     * @return false|int
     * @since 4.0.0
     */
    public function incrementVotes($pollId, $choices)
    {
        $choices = array_filter(
            $choices,
            function ($choice) {
                return $choice['votes'] >= 1;
            }
        );

        if (empty($choices)) {
            return true;
        }

        // Preparing SQL statement (INSERT INTO ... ON DUPLICATE KEY UPDATE)
        $query = "INSERT INTO %s (choice_uid, poll_id, votes, last_vote_at) VALUES %s ON DUPLICATE KEY UPDATE votes = (votes+(CASE choice_uid %s END)), last_vote_at = CURRENT_TIME()";
        $values = [];
        $votes = [];
        foreach ((array)$choices as $choice):
            $values[] = $this->database->prepare('(%s, %d, %d, CURRENT_TIME())', (string)$choice['uid'], (int)$pollId, absint($choice['votes']));
            $votes[] = $this->database->prepare('WHEN %s THEN %d', (string)$choice['uid'], absint($choice['votes']));
        endforeach;
        // Replace placeholders
        $query = sprintf($query, $this->env['db.tables.votes'], implode(', ', $values), implode(' ', $votes));

        // Execute
        return $this->database->query($query);
    }

    /**
     * Set choice(s) votes.
     *
     * @param  int  $pollId
     * @param  array  $choicesUidsVotes
     *
     * @return false|int
     * @since 4.0.0
     */
    public function setVotes($pollId, $choicesUidsVotes)
    {
        if (empty($pollId) || empty($choicesUidsVotes)):
            return false;
        endif;

        // Preparing SQL statement (INSERT INTO ... ON DUPLICATE KEY UPDATE)
        $query = "INSERT INTO %s (choice_uid, poll_id, votes, last_vote_at) VALUES %s ON DUPLICATE KEY UPDATE votes = (CASE choice_uid %s END), last_vote_at = CURRENT_TIME()";
        $values = [];
        $votes = [];
        foreach ((array)$choicesUidsVotes as $choiceUid => $choiceVotes):
            $values[] = $this->database->prepare('(%s, %d, %d, CURRENT_TIME())', (string)$choiceUid, (int)$pollId, (int)$choiceVotes);
            $votes[] = $this->database->prepare('WHEN %s THEN %d', (string)$choiceUid, (int)$choiceVotes);
        endforeach;
        // Replace placeholders
        $query = sprintf($query, $this->env['db.tables.votes'], implode(', ', $values), implode(' ', $votes));

        // Execute
        return $this->database->query($query);
    }

    /**
     * Delete votes.
     *
     * @param $query array
     *
     * @return bool|\WP_Error
     * @since 4.0.5
     */
    public function deleteVotes($query)
    {
        $where = Sql::generateWhereClause($query);

        if (empty($where)):
            return new \WP_Error('no_conditions', esc_html__('No conditions were specified', 'totalpoll'));
        endif;

        $query = "DELETE FROM `{$this->env['db.tables.votes']}` {$where}";

        return (bool)$this->database->query($query);
    }

    /**
     * Count polls.
     *
     * @return int
     * @since 4.3.0
     */
    public function count()
    {
        return (int)wp_count_posts(TP_POLL_CPT_NAME)->publish;
    }

    /**
     * @return int
     */
    public function countVotes()
    {
        $votesTable = Application::getInstance()
                                 ->env('db.tables.votes');
        return (int)$this->database->get_var("SELECT SUM(votes) FROM {$votesTable}");
    }
}
