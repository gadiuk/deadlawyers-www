<?php

namespace TotalPoll\Decorators;


use TotalPoll\Poll\Model;

/**
 * Class StructuredData
 *
 * @package TotalPoll\Decorators
 */
class StructuredData {
	/**
	 * StructuredData constructor.
	 */
	public function __construct() {
		add_filter( 'totalpoll/filters/render/args', [ $this, 'snippet' ], 10, 2 );
	}

	public function snippet( $args, Model $poll ) {
		$question = current( $poll->getQuestions( Model::SORT_BY_VOTES, Model::SORT_DESC ) );
		$date     = date( DATE_W3C, strtotime( $poll->getPollPost()->post_date ) );
		$author   = [
			"@type" => "Organization",
			"name"  => get_bloginfo( 'name' ),
			"url"   => get_bloginfo( 'url' ),
		];

		$schemaChoices = [];
		foreach ( $question['choices'] as $index => $choice ) {
			$schemaChoice = [
				"@type"         => "Answer",
				"text"          => $choice['label'],
				"upvoteCount"   => $choice['votes'],
				"url"           => add_query_arg( [ 'choice' => $choice['uid'] ] ),
				"datePublished" => $date,
				"author"        => $author,
			];

			if ( $choice['type'] === 'image' ) {
				$schemaChoice['image'] = $choice['image']['thumbnail'];
			}

			$schemaChoices[] = $schemaChoice;

			if ( $index === 0 ) {
				$topChoice = $schemaChoice;
			}
		}

		$structure = [
			"@context"   => "https://schema.org",
			"@type"      => "QAPage",
			"mainEntity" => [
				"@type"           => "Question",
				"name"            => $question['content'],
				"text"            => $question['content'],
				"answerCount"     => count( $schemaChoices ),
				"upvoteCount"     => $poll->getTotalVotes(),
				"datePublished"   => $date,
				"author"          => $author,
				"suggestedAnswer" => $schemaChoices,
			],
		];

		if ( isset( $topChoice ) ) {
			$structure["acceptedAnswer"] = [ $topChoice ];
		}

		$args['before'] = sprintf(
			'<script type="application/ld+json">%s</script>',
			json_encode( $structure, JSON_PRETTY_PRINT )
		);

		return $args;
	}
}
