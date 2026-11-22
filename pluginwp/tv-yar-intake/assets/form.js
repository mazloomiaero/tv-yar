(function () {
  function ready(fn) {
    if (document.readyState !== 'loading') {
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  }

  ready(function () {
    var form = document.querySelector('[data-tv-yar-intake-form]');
    if (!form || typeof tvYarIntake === 'undefined') {
      return;
    }

    var message = form.querySelector('[data-tv-yar-intake-message]');
    var submit = form.querySelector('.tv-yar-intake__submit');

    form.addEventListener('submit', function (event) {
      event.preventDefault();

      if (!message || !submit) {
        return;
      }

      message.hidden = true;
      message.className = 'tv-yar-intake__message';
      submit.disabled = true;

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
          message.hidden = false;
          message.textContent = result.data.message || (result.ok ? 'ثبت شد.' : 'خطا در ثبت.');
          message.classList.add(result.ok ? 'is-success' : 'is-error');
          if (result.ok) {
            form.reset();
          }
        })
        .catch(function () {
          message.hidden = false;
          message.textContent = 'ارتباط با سرور برقرار نشد.';
          message.classList.add('is-error');
        })
        .finally(function () {
          submit.disabled = false;
        });
    });
  });
})();
