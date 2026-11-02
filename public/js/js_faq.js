function toggleFaq(i) {
    var cards = document.querySelectorAll('.faq-card');
    cards.forEach(function(c, idx) {
        var a = document.getElementById('faqAnswer' + idx);
        if (idx === i && !c.classList.contains('open')) {
            c.classList.add('open');
            a.style.maxHeight = a.scrollHeight + 'px';
        } else {
            c.classList.remove('open');
            if (a) a.style.maxHeight = '0';
        }
    });
}

function filterCategory(cat, el) {
    document.querySelectorAll('.faq-tab').forEach(function(t) { t.classList.remove('active'); });
    el.classList.add('active');
    // close all
    document.querySelectorAll('.faq-card').forEach(function(c, idx) {
        c.classList.remove('open');
        var a = document.getElementById('faqAnswer' + idx);
        if (a) a.style.maxHeight = '0';
    });
    var vis = 0;
    document.querySelectorAll('.faq-card').forEach(function(c) {
        if (cat === 'All' || c.dataset.category === cat) { c.style.display = ''; vis++; }
        else { c.style.display = 'none'; }
    });
    document.getElementById('noResultsMsg').style.display = vis ? 'none' : 'block';
}

function filterFaqs() {
    var q = document.getElementById('faqSearchInput').value.toLowerCase().trim();
    var vis = 0;
    document.querySelectorAll('.faq-card').forEach(function(c) {
        var t = c.querySelector('.faq-title').innerText.toLowerCase();
        var a = c.querySelector('.faq-a-inner').innerText.toLowerCase();
        if (t.indexOf(q) > -1 || a.indexOf(q) > -1) { c.style.display = ''; vis++; }
        else { c.style.display = 'none'; }
    });
    document.getElementById('noResultsMsg').style.display = vis ? 'none' : 'block';
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof gsap !== 'undefined') {
        gsap.from('#faqSearchInput, .faq-tab', {
            y: -20,
            opacity: 0,
            duration: 0.65,
            stagger: 0.08,
            ease: 'power3.out'
        });

        gsap.from('.faq-card', {
            y: 35,
            opacity: 0,
            duration: 0.75,
            stagger: 0.07,
            ease: 'power3.out',
            delay: 0.15
        });
    }
});
