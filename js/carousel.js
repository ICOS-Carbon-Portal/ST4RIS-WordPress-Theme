/* ST4RIS staff story carousel - vanilla JS, no dependencies */
document.addEventListener('DOMContentLoaded', function () {
  const track = document.getElementById('st4ris-track') || document.querySelector('.story-track');
  if (!track || !track.children.length) return;

  const dots = document.querySelectorAll('.st4ris-dot, .story-dots .dot');
  const btnPrev = document.getElementById('st4ris-prev');
  const btnNext = document.getElementById('st4ris-next');
  const total = track.children.length;
  let cur = 0;
  let timer = null;

  function goTo(n) {
    cur = (n + total) % total;
    track.style.transform = 'translateX(-' + (cur * 100) + '%)';
    dots.forEach(function (dot, i) { dot.classList.toggle('active', i === cur); });
  }
  function next() { goTo(cur + 1); }
  function prev() { goTo(cur - 1); }
  function startTimer() { timer = window.setInterval(next, 6000); }
  function resetTimer() { window.clearInterval(timer); startTimer(); }

  dots.forEach(function (dot, i) {
    dot.type = 'button';
    if (!dot.getAttribute('aria-label')) dot.setAttribute('aria-label', 'Story ' + (i + 1));
    dot.addEventListener('click', function () { goTo(i); resetTimer(); });
  });
  if (btnPrev) btnPrev.addEventListener('click', function () { prev(); resetTimer(); });
  if (btnNext) btnNext.addEventListener('click', function () { next(); resetTimer(); });

  track.addEventListener('mouseenter', function () { window.clearInterval(timer); });
  track.addEventListener('mouseleave', startTimer);

  let startX = 0;
  track.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
  track.addEventListener('touchend', function (e) {
    const dx = e.changedTouches[0].clientX - startX;
    if (Math.abs(dx) > 50) { dx < 0 ? next() : prev(); resetTimer(); }
  });

  startTimer();
});
