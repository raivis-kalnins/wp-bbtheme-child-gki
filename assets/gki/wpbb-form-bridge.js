(function () {
  'use strict';

  var cfg = window.wpbbForm || window.gkiWpbbFormBridge || {};
  var ajaxUrl = cfg.ajaxUrl || '';
  var nonce = cfg.nonce || '';
  var defaultError = cfg.error || 'Something went wrong. Please try again.';
  var validationText = cfg.validationText || 'Please fill in all required fields.';

  function closest(el, selector) {
    while (el && el.nodeType === 1) {
      if (el.matches(selector)) return el;
      el = el.parentElement;
    }
    return null;
  }

  function fieldLabel(field) {
    var id = field.getAttribute('id');
    var label = id ? document.querySelector('label[for="' + CSS.escape(id) + '"]') : null;
    if (!label) {
      var wrap = closest(field, '.wpbb-field, .gki-form-field, .col-12, .col-6');
      label = wrap ? wrap.querySelector('label') : null;
    }
    return label ? label.textContent.replace('*', '').trim() : (field.getAttribute('name') || 'Field');
  }

  function collectFields(form) {
    var fields = [];
    form.querySelectorAll('input, select, textarea').forEach(function (el) {
      var name = el.getAttribute('name');
      if (!name || name === 'wpbb_captcha_enabled' || name === 'wpbb_captcha_provider' || name === 'h-captcha-response' || name === 'g-recaptcha-response' || name === 'started_at') return;
      if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) return;
      if (el.type === 'file') return;
      fields.push({ name: name.replace(/\[\]$/, ''), label: fieldLabel(el), value: el.value || '' });
    });
    return fields;
  }

  function setMessage(form, text, ok) {
    var msg = form.querySelector('.wpbb-form-message');
    if (!msg) {
      msg = document.createElement('div');
      msg.className = 'wpbb-form-message mt-3';
      msg.setAttribute('aria-live', 'polite');
      form.appendChild(msg);
    }
    msg.textContent = text || '';
    msg.classList.toggle('is-success', !!ok);
    msg.classList.toggle('is-error', !ok);
  }

  function renderHcaptchaWidgets() {
    if (!window.hcaptcha || typeof window.hcaptcha.render !== 'function') return false;
    var rendered = false;
    document.querySelectorAll('.gki-form-card .h-captcha:not([data-gki-hcaptcha-rendered])').forEach(function (el) {
      var sitekey = el.getAttribute('data-sitekey');
      if (!sitekey) return;
      try {
        window.hcaptcha.render(el, {
          sitekey: sitekey,
          hl: el.getAttribute('data-hl') || undefined
        });
        el.setAttribute('data-gki-hcaptcha-rendered', '1');
        rendered = true;
      } catch (e) {
        if (String(e && e.message || '').indexOf('already rendered') !== -1) {
          el.setAttribute('data-gki-hcaptcha-rendered', '1');
        }
      }
    });
    return rendered;
  }

  function bootCaptchas() {
    var attempts = 0;
    renderHcaptchaWidgets();
    var timer = window.setInterval(function () {
      attempts += 1;
      if (renderHcaptchaWidgets() || attempts > 30) {
        window.clearInterval(timer);
      }
    }, 250);
  }

  function submit(form) {
    if (!ajaxUrl) return;
    if (!form.checkValidity()) {
      form.reportValidity();
      setMessage(form, form.getAttribute('data-validation') || validationText, false);
      return;
    }
    var data = new FormData(form);
    data.append('action', 'wpbb_submit_form');
    data.append('nonce', nonce);
    data.append('fields', JSON.stringify(collectFields(form)));
    data.append('settings', JSON.stringify({
      recipient: form.getAttribute('data-recipient') || '',
      email_subject: form.getAttribute('data-subject') || '',
      success_message: form.getAttribute('data-success') || ''
    }));

    var button = form.querySelector('[type="submit"]');
    if (button) button.disabled = true;
    setMessage(form, '', true);

    fetch(ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' })
      .then(function (r) { return r.json().catch(function () { return {}; }); })
      .then(function (json) {
        if (json && json.success) {
          setMessage(form, (json.data && json.data.message) || form.getAttribute('data-success') || 'Thank you!', true);
          form.reset();
          if (window.hcaptcha) { try { window.hcaptcha.reset(); } catch (e) {} }
          if (window.grecaptcha) { try { window.grecaptcha.reset(); } catch (e) {} }
        } else {
          setMessage(form, (json && json.data && json.data.message) || defaultError, false);
        }
      })
      .catch(function () { setMessage(form, defaultError, false); })
      .finally(function () { if (button) button.disabled = false; });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootCaptchas);
  } else {
    bootCaptchas();
  }

  document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!form || !form.matches || !form.matches('.wpbb-dynamic-form, .gki-bbuilder-form')) return;
    if (!form.querySelector('input[name="wpbb_captcha_enabled"], .h-captcha, .g-recaptcha') && !form.classList.contains('wpbb-dynamic-form')) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    submit(form);
  }, true);
})();
