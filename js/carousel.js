/* ST4RIS staff story carousel — vanilla JS, no dependencies */
document.addEventListener( 'DOMContentLoaded', function () {
    const track  = document.getElementById( 'st4ris-track' );
    const dots   = document.querySelectorAll( '.st4ris-dot' );
    if ( ! track ) return;

    const total  = track.children.length;
    let   cur    = 0;
    let   timer  = null;

    function goTo( n ) {
        cur = ( n + total ) % total;
        track.style.transform = 'translateX(-' + ( cur * 100 ) + '%)';
        dots.forEach( function ( d, i ) {
            d.classList.toggle( 'active', i === cur );
        } );
    }

    function next() { goTo( cur + 1 ); }
    function prev() { goTo( cur - 1 ); }

    function startTimer() {
        timer = setInterval( next, 6000 );
    }
    function resetTimer() {
        clearInterval( timer );
        startTimer();
    }

    // Dot buttons
    dots.forEach( function ( dot, i ) {
        dot.addEventListener( 'click', function () {
            goTo( i );
            resetTimer();
        } );
    } );

    // Prev / Next buttons
    var btnPrev = document.getElementById( 'st4ris-prev' );
    var btnNext = document.getElementById( 'st4ris-next' );
    if ( btnPrev ) btnPrev.addEventListener( 'click', function () { prev(); resetTimer(); } );
    if ( btnNext ) btnNext.addEventListener( 'click', function () { next(); resetTimer(); } );

    // Pause on hover
    track.addEventListener( 'mouseenter', function () { clearInterval( timer ); } );
    track.addEventListener( 'mouseleave', startTimer );

    // Touch swipe support
    var startX = 0;
    track.addEventListener( 'touchstart', function ( e ) { startX = e.touches[0].clientX; }, { passive: true } );
    track.addEventListener( 'touchend',   function ( e ) {
        var dx = e.changedTouches[0].clientX - startX;
        if ( Math.abs( dx ) > 50 ) { dx < 0 ? next() : prev(); resetTimer(); }
    } );

    startTimer();
} );
