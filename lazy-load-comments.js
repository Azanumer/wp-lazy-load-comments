/**
 * Lazy Load Comments — fetches rendered comments when the placeholder
 * scrolls into view. No libraries; IntersectionObserver with a scroll
 * fallback for very old browsers.
 */
(function () {
    'use strict';

    var placeholder = document.getElementById('wllc-comments-placeholder');
    if (!placeholder || !window.wllcData) {
        return;
    }

    var loaded = false;

    function loadComments() {
        if (loaded) {
            return;
        }
        loaded = true;
        fetch(wllcData.restUrl + wllcData.postId)
            .then(function (response) { return response.json(); })
            .then(function (data) {
                var wrapper = document.createElement('div');
                wrapper.innerHTML = data.html || '';
                placeholder.parentNode.replaceChild(wrapper, placeholder);
            })
            .catch(function () {
                // On failure, leave a retry link instead of a blank area.
                placeholder.innerHTML = '<p>Could not load comments. <a href="#" id="wllc-retry">Try again</a></p>';
                document.getElementById('wllc-retry').addEventListener('click', function (e) {
                    e.preventDefault();
                    loaded = false;
                    loadComments();
                });
            });
    }

    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    observer.disconnect();
                    loadComments();
                }
            });
        }, { rootMargin: '400px' }); // start loading slightly before it is visible
        observer.observe(placeholder);
    } else {
        window.addEventListener('scroll', loadComments, { once: true });
    }
})();
