<div class="totalpoll-settings-item totalpoll-pro-badge-container">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'URL', 'totalpoll' ); ?>
        </label>
        <input type="text" class="totalpoll-settings-field-input widefat" ng-model="editor.settings.notifications.webhook.url" dir="ltr">
    </div>
    <p class="totalpoll-feature-tip">
		<?php esc_html_e( 'This URL will receive an HTTP POST with the following body', 'totalpoll' ); ?>
    </p>
    <div class="totalpoll-code-sample">
        <pre style="font-family: monospace; font-size: 9.6pt;">{<br>  <span style="color:#660e7a;">"id"</span>: <span style="color:#0000ff;">123</span>,<br>  <span
                    style="color:#660e7a;">"title"</span>: <span style="color:#008000;">"Poll title"</span>,<br>  <span style="color:#660e7a;">"questions"</span>: {<br>    <span
                    style="color:#660e7a;">"e559ae6f-7bd8-4567-bf3d-55205e19ba2f"</span>: {<br>      <span style="color:#660e7a;">"uid"</span>: <span style="color:#008000;">"e559ae6f-7bd8-4567-bf3d-55205e19ba2f"</span>,<br>      <span
                    style="color:#660e7a;">"content"</span>: <span style="color:#008000;">"Question content"</span>,<br>      <span style="color:#660e7a;">"choices"</span>: {<br>        <span
                    style="color:#660e7a;">"aa53d36c-80e6-404b-b62e-719cb63cbd2d"</span>: {<br>          <span style="color:#660e7a;">"uid"</span>: <span style="color:#008000;">"aa53d36c-80e6-404b-b62e-719cb63cbd2d"</span>,<br>          <span
                    style="color:#660e7a;">"type"</span>: <span style="color:#008000;">"text"</span>,<br>          <span style="color:#660e7a;">"label"</span>: <span
                    style="color:#008000;">"First"</span>,<br>          <span style="color:#660e7a;">"votes"</span>: <span style="color:#0000ff;">0</span>,<br>          <span
                    style="color:#660e7a;">"questionUid"</span>: <span style="color:#008000;">"e559ae6f-7bd8-4567-bf3d-55205e19ba2f"</span>,<br>          <span
                    style="color:#660e7a;">"receivedVotes"</span>: <span style="color:#0000ff;">0<br></span><span style="color:#0000ff;">        </span>},<br>        <span
                    style="color:#660e7a;">"a5744203-72ea-4dc8-96b7-28697c404698"</span>: {<br>          <span style="color:#660e7a;">"uid"</span>: <span style="color:#008000;">"a5744203-72ea-4dc8-96b7-28697c404698"</span>,<br>          <span
                    style="color:#660e7a;">"type"</span>: <span style="color:#008000;">"text"</span>,<br>          <span style="color:#660e7a;">"label"</span>: <span
                    style="color:#008000;">"Second"</span>,<br>          <span style="color:#660e7a;">"votes"</span>: <span style="color:#0000ff;">5</span>,<br>          <span
                    style="color:#660e7a;">"questionUid"</span>: <span style="color:#008000;">"e559ae6f-7bd8-4567-bf3d-55205e19ba2f"</span>,<br>          <span
                    style="color:#660e7a;">"receivedVotes"</span>: <span style="color:#0000ff;">1<br></span><span style="color:#0000ff;">        </span>},<br>        <span
                    style="color:#660e7a;">"f3232183-3965-4189-b3e2-d0bf9804ab3f"</span>: {<br>          <span style="color:#660e7a;">"uid"</span>: <span style="color:#008000;">"f3232183-3965-4189-b3e2-d0bf9804ab3f"</span>,<br>          <span
                    style="color:#660e7a;">"type"</span>: <span style="color:#008000;">"text"</span>,<br>          <span style="color:#660e7a;">"label"</span>: <span
                    style="color:#008000;">"Third"</span>,<br>          <span style="color:#660e7a;">"votes"</span>: <span style="color:#0000ff;">19</span>,<br>          <span
                    style="color:#660e7a;">"questionUid"</span>: <span style="color:#008000;">"e559ae6f-7bd8-4567-bf3d-55205e19ba2f"</span>,<br>          <span
                    style="color:#660e7a;">"receivedVotes"</span>: <span style="color:#0000ff;">1<br></span><span style="color:#0000ff;">        </span>}<br>      },<br>      <span
                    style="color:#660e7a;">"settings"</span>: {<br>        <span style="color:#660e7a;">"selection"</span>: {<br>          <span
                    style="color:#660e7a;">"minimum"</span>: <span style="color:#0000ff;">2</span>,<br>          <span style="color:#660e7a;">"maximum"</span>: <span
                    style="color:#0000ff;">2<br></span><span style="color:#0000ff;">        </span>}<br>      },<br>      <span style="color:#660e7a;">"votes"</span>: <span
                    style="color:#0000ff;">24</span>,<br>      <span style="color:#660e7a;">"receivedVotes"</span>: <span style="color:#0000ff;">2<br></span><span
                    style="color:#0000ff;">    </span>}<br>  },<br>  <span style="color:#660e7a;">"receivedVotes"</span>: {<br>    <span style="color:#660e7a;">"a5744203-72ea-4dc8-96b7-28697c404698"</span>: {<br>      <span
                    style="color:#660e7a;">"uid"</span>: <span style="color:#008000;">"a5744203-72ea-4dc8-96b7-28697c404698"</span>,<br>      <span
                    style="color:#660e7a;">"type"</span>: <span style="color:#008000;">"text"</span>,<br>      <span style="color:#660e7a;">"label"</span>: <span
                    style="color:#008000;">"Second"</span>,<br>      <span style="color:#660e7a;">"votes"</span>: <span style="color:#0000ff;">5</span>,<br>      <span
                    style="color:#660e7a;">"questionUid"</span>: <span style="color:#008000;">"e559ae6f-7bd8-4567-bf3d-55205e19ba2f"</span>,<br>      <span style="color:#660e7a;">"receivedVotes"</span>: <span
                    style="color:#0000ff;">1<br></span><span style="color:#0000ff;">    </span>},<br>    <span style="color:#660e7a;">"f3232183-3965-4189-b3e2-d0bf9804ab3f"</span>: {<br>      <span
                    style="color:#660e7a;">"uid"</span>: <span style="color:#008000;">"f3232183-3965-4189-b3e2-d0bf9804ab3f"</span>,<br>      <span
                    style="color:#660e7a;">"type"</span>: <span style="color:#008000;">"text"</span>,<br>      <span style="color:#660e7a;">"label"</span>: <span
                    style="color:#008000;">"Third"</span>,<br>      <span style="color:#660e7a;">"votes"</span>: <span style="color:#0000ff;">19</span>,<br>      <span
                    style="color:#660e7a;">"questionUid"</span>: <span style="color:#008000;">"e559ae6f-7bd8-4567-bf3d-55205e19ba2f"</span>,<br>      <span style="color:#660e7a;">"receivedVotes"</span>: <span
                    style="color:#0000ff;">1<br></span><span style="color:#0000ff;">    </span>}<br>  }<br>}</pre>
    </div>
    <?php TotalPoll( 'upgrade-to-pro' ); ?>
</div>
<div class="totalpoll-settings-item totalpoll-pro-badge-container">
    <p>
		<?php esc_html_e( 'Send notification when', 'totalpoll' ); ?>
    </p>
    <div class="totalpoll-settings-field">
        <label>
            <input type="checkbox" name="" ng-model="editor.settings.notifications.webhook.on.newVote" ng-checked="editor.settings.notifications.webhook.on.newVote">
			<?php esc_html_e( 'New vote has been casted', 'totalpoll' ); ?>
        </label>
    </div>
    <?php TotalPoll( 'upgrade-to-pro' ); ?>
</div>
