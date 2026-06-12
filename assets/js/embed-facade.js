/**
 * Click-to-play embed facades in microblog preview cards.
 */
(function() {
    'use strict';

    function activateFacade(facade) {
        if (!facade || facade.classList.contains('is-playing')) {
            return;
        }

        var embedSrc = facade.getAttribute('data-embed-src');
        if (!embedSrc) {
            return;
        }

        var frame = facade.querySelector('.embed-facade__frame');
        if (!frame) {
            return;
        }

        var iframe = document.createElement('iframe');
        var separator = embedSrc.indexOf('?') === -1 ? '?' : '&';

        iframe.src = embedSrc + separator + 'autoplay=1';
        iframe.title = facade.getAttribute('data-embed-title') || 'Embedded video';
        iframe.setAttribute('loading', 'lazy');
        iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
        iframe.setAttribute('allowfullscreen', '');
        iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');

        frame.replaceChildren(iframe);
        facade.classList.add('is-playing');
    }

    document.addEventListener('click', function(event) {
        var facade = event.target.closest('.post-preview--microblog .embed-facade');
        if (!facade || facade.classList.contains('is-playing')) {
            return;
        }

        var playButton = event.target.closest('.embed-facade__play');
        if (!playButton || !facade.contains(playButton)) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
        activateFacade(facade);
    }, true);
})();
