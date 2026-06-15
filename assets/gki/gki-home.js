(function(){
  'use strict';
  function qs(s,r){return (r||document).querySelector(s)}
  function qsa(s,r){return Array.prototype.slice.call((r||document).querySelectorAll(s))}
  function assetBase(){
    var scripts=qsa('script[src*="/assets/gki/gki-home.js"]');
    if(scripts.length){return scripts[0].src.replace(/gki-home\.js.*$/,'')}
    var img=qs('.gki-logo img[src*="/assets/gki/"]');
    if(img){return img.src.replace(/[^/]+$/,'')}
    return '/wp-content/themes/wp-bbtheme-child-gki/assets/gki/';
  }
  function currentYear(){
    qsa('.gki-current-year').forEach(function(el){el.textContent=(new Date()).getFullYear();});
  }
  function serviceIcons(){
    var map={
      plumbing:'icon-plumbing-v52.svg',
      mechanical:'icon-mechanical-v52.svg',
      facilities:'icon-facilities-v52.svg',
      welding:'icon-welding-v52.svg',
      fabrication:'icon-fabrication-v52.svg'
    };
    var base=assetBase();
    qsa('#gki-site .gki-service-card').forEach(function(card){
      var label=(card.textContent||'').toLowerCase();
      Object.keys(map).forEach(function(key){
        if(label.indexOf(key)!==-1){
          var img=card.querySelector('.gki-service-icon img, figure img');
          if(img && img.src.indexOf(map[key])===-1){
            img.src=base+map[key];
            img.removeAttribute('srcset');
            img.removeAttribute('sizes');
          }
        }
      });
    });
  }
  function fixForm(){
    qsa('.gki-form-card form').forEach(function(form){
      form.classList.add('gki-bbuilder-form');
      qsa('select[name="language"], select[name="lang"]', form).forEach(function(el){
        var wrap=el.closest('.wpbb-field,[class*="col-"],.form-group')||el.parentNode;
        if(wrap) wrap.style.display='none';
      });
      qsa('input[name="website"], input[name="url"], input[name="honeypot"]', form).forEach(function(el){
        var wrap=el.closest('.wpbb-field,[class*="col-"],.form-group')||el;
        if(wrap) wrap.style.display='none';
      });
    });
    qsa('body > .h-captcha, body > [class*="hcaptcha"]').forEach(function(el){
      if(!el.closest('.gki-form-card')) el.style.display='none';
    });
  }
  function renderHcaptcha(){
    if(!window.hcaptcha || typeof window.hcaptcha.render!=='function') return;
    qsa('.gki-form-card .h-captcha').forEach(function(el){
      if(el.dataset.gkiRendered==='1' || el.dataset.srHcaptchaRendered==='1') return;
      try{ window.hcaptcha.render(el); el.dataset.gkiRendered='1'; }catch(e){
        if(String(e && e.message || '').indexOf('already rendered')!==-1){ el.dataset.gkiRendered='1'; }
      }
    });
  }
  function projectImages(){
    var base=assetBase();
    var first=qs('#gki-site .gki-projects .gki-project:first-child img, #gki-site .gki-projects > *:first-child img');
    if(first){
      first.src=base+'project-restaurant-v52.avif';
      first.removeAttribute('srcset');
      first.removeAttribute('sizes');
      first.alt=first.alt || 'Restaurant maintenance project';
    }
  }
  function testimonials(){
    qsa('.gki-swiper').forEach(function(swiper){
      var slides=qsa('.gki-testimonial-card',swiper);
      if(!slides.length) return;
      var menu=swiper.parentNode && swiper.parentNode.querySelector('.gki-slider-menu');
      swiper.classList.add('gki-swiper-ready');
      var i=0,timer;
      function show(n){
        i=(n+slides.length)%slides.length;
        slides.forEach(function(s,k){s.classList.toggle('is-active',k===i);});
        if(menu){qsa('button',menu).forEach(function(b,k){b.classList.toggle('is-active',k===i);});}
      }
      if(menu && !menu.children.length){
        slides.forEach(function(_,k){
          var b=document.createElement('button');
          b.type='button';
          b.setAttribute('aria-label','Show testimonial '+(k+1));
          b.addEventListener('click',function(){show(k);restart();});
          menu.appendChild(b);
        });
      }
      function restart(){clearInterval(timer);timer=setInterval(function(){show(i+1);},5500);}
      show(0);restart();
    });
  }

  function mobileMenu(){
    qsa('.gki-header').forEach(function(header){
      var toggle=qs('.gki-menu-toggle',header);
      if(toggle && !toggle.dataset.gkiReady){
        if(!toggle.querySelector('span')) toggle.appendChild(document.createElement('span'));
        toggle.setAttribute('aria-label','Toggle navigation');
        toggle.setAttribute('aria-expanded','false');
        toggle.dataset.gkiReady='1';
        toggle.addEventListener('click',function(){
          var open=!header.classList.contains('is-open');
          header.classList.toggle('is-open',open);
          toggle.setAttribute('aria-expanded',open?'true':'false');
        });
      }
      qsa('a[href^="#"], a[href^="/#"]',header).forEach(function(a){
        if(a.dataset.gkiCloseReady) return;
        a.dataset.gkiCloseReady='1';
        a.addEventListener('click',function(){
          header.classList.remove('is-open');
          if(toggle) toggle.setAttribute('aria-expanded','false');
        });
      });
    });
  }

  function scrollTop(){
    var b=qs('.gki-scrolltop'); if(!b) return;
    function u(){b.classList.toggle('is-visible',window.scrollY>450);}
    u(); window.addEventListener('scroll',u,{passive:true});
    b.addEventListener('click',function(){window.scrollTo({top:0,behavior:'smooth'});});
  }
  function boot(){
    currentYear(); serviceIcons(); projectImages(); fixForm(); testimonials(); renderHcaptcha(); mobileMenu(); scrollTop();
    setTimeout(function(){serviceIcons();projectImages();fixForm();renderHcaptcha();},800);
    setTimeout(function(){serviceIcons();projectImages();fixForm();renderHcaptcha();},1600);
  }
  if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',boot);}else{boot();}
})();