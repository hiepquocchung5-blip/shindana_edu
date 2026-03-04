// assets/js/main.js

// 1. Google Translate Initialization
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'en',
        includedLanguages: 'en,my,ja', // English, Myanmar, Japanese
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
        autoDisplay: false
    }, 'google_translate_element');
}

// Load Google Script Dynamically if not in head
(function() {
    var gtScript = document.createElement('script');
    gtScript.type = 'text/javascript';
    gtScript.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
    document.body.appendChild(gtScript);
})();

// 2. Sticky Nav Logic (Optional vanilla JS enhancement to Alpine)
document.addEventListener('scroll', function() {
    const nav = document.querySelector('nav');
    if (window.scrollY > 50) {
        nav.classList.add('shadow-lg', 'bg-white/95');
        nav.classList.remove('bg-white/90');
    } else {
        nav.classList.remove('shadow-lg', 'bg-white/95');
        nav.classList.add('bg-white/90');
    }
});