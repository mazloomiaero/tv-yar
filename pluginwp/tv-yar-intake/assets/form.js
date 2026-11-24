(function () {
  function ready(fn) {
    if (document.readyState !== 'loading') {
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  }

  ready(function () {
    var root = document.querySelector('[data-tv-yar-intake]');
    var form = document.querySelector('[data-tv-yar-intake-form]');
    if (!root || !form || typeof tvYarIntake === 'undefined') {
      return;
    }

    var message = root.querySelector('[data-tv-yar-intake-message]');
    var submit = form.querySelector('.tv-yar-intake__submit');
    var successPanel = root.querySelector('[data-tv-yar-intake-success]');
    var successText = root.querySelector('[data-tv-yar-intake-success-text]');
    var header = root.querySelector('[data-tv-yar-intake-header]');

    form.addEventListener('submit', function (event) {
      event.preventDefault();

      if (!message || !submit) {
        return;
      }

      message.hidden = true;
      message.className = 'tv-yar-intake__message';
      submit.disabled = true;
      submit.dataset.originalLabel = submit.textContent || '';
      submit.textContent = 'در حال ثبت...';

      var payload = {
        full_name: (form.querySelector('[name="full_name"]') || {}).value || '',
        mobile: (form.querySelector('[name="mobile"]') || {}).value || '',
        brand: (form.querySelector('[name="brand"]') || {}).value || '',
        problem: (form.querySelector('[name="problem"]') || {}).value || '',
        address: (form.querySelector('[name="address"]') || {}).value || ''
      };

      fetch(tvYarIntake.endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': tvYarIntake.nonce
        },
        body: JSON.stringify(payload)
      })
        .then(function (response) {
          return response.json().then(function (data) {
            return { ok: response.ok && data && data.ok, data: data || {} };
          });
        })
        .then(function (result) {
          if (result.ok) {
            form.hidden = true;
            if (header) {
              header.hidden = true;
            }
            if (successPanel && successText) {
              successText.textContent = result.data.message || 'درخواست شما ثبت شد.';
              successPanel.hidden = false;
            } else {
              message.hidden = false;
              message.textContent = result.data.message || 'درخواست شما ثبت شد.';
              message.classList.add('is-success');
            }
            return;
          }

          message.hidden = false;
          message.textContent = result.data.message || 'خطا در ثبت.';
          message.classList.add('is-error');
        })
        .catch(function () {
          message.hidden = false;
          message.textContent = 'ارتباط با سرور برقرار نشد.';
          message.classList.add('is-error');
        })
        .finally(function () {
          if (!form.hidden) {
            submit.disabled = false;
            submit.textContent = submit.dataset.originalLabel || 'ثبت درخواست';
          }
        });
    });
  });
})();
