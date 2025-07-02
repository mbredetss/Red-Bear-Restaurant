// Intersection Observer for fade-in effect
document.addEventListener('DOMContentLoaded', function () {
    var locationSection = document.getElementById('location');
    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    locationSection.classList.add('visible');
                    observer.disconnect();
                }
            });
        }, { threshold: 0.2 });
        observer.observe(locationSection);
    } else {
        // Fallback
        locationSection.classList.add('visible');
    }
});