<style>
    #feature-request-form .totalpoll-settings-field {
        display: flex;
        align-items: center;
    }

    #feature-request-form .totalpoll-form-field-input {
        border-radius: 4px 0 0 4px;
    }

    #feature-request-form .button {
        border-radius: 0 4px 4px 0;
    }

    #feature-request-form .totalpoll-message {
        background: #fdf3d6;
        margin: -6px -12px 12px;
        padding: 12px;
    }
</style>
<div id="feature-request-form">
    <p class="totalpoll-message"><?php esc_html_e('Have you got an idea or improvement? We will be delighted to hear it.') ?></p>
    <label class="totalpoll-settings-field-label" for="feature_request_title"><?php esc_html_e( 'Feature title', 'totalpoll' ); ?></label>
    <div class="totalpoll-settings-field">
        <input name="search" type="text" id="feature_request_title" class="totalpoll-form-field-input widefat">
        <button type="button" class="button button-primary"><?php esc_html_e( 'Continue', 'totalpoll' ); ?></button>
    </div>
</div>
<script>
    var featureRequestForm = document.querySelector('#feature-request-form');
    var form = document.createElement('form');
    form.action = "https://totalsuite.net/feature-requests-board/";
    form.method = "get";
    form.target = "_blank";
    form.hidden = true;

    document.body.append(form);

    var featureRequestFormListener = function () {
        form.innerHTML = '';
        featureRequestForm.querySelectorAll('input, textarea, select').forEach(function (element) {
            form.append(element.cloneNode());
        });

        form.submit();

        return false;
    };

    featureRequestForm.querySelector('button').addEventListener('click', featureRequestFormListener, false);
    featureRequestForm.addEventListener('keydown', function (event) {
        if (event.keyCode === 13) {
            featureRequestFormListener();
        }
    }, false);
</script>
