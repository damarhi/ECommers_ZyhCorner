document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.hero-slide-item');
    const dots = document.querySelectorAll('.hero-dot');
    const prevBtn = document.querySelector('.hero-slider-prev');
    const nextBtn = document.querySelector('.hero-slider-next');
    const wrapper = document.querySelector('.hero-slider-wrapper');

    if (!slides.length) return;

    let currentSlide = 0;
    let slideInterval;
    let isAnimating = false;
    let floatTween = null;

    function startImageFloat(slide) {
        if (floatTween) floatTween.kill();
        const img = slide.querySelector('.hero-image img');
        if (img && typeof gsap !== 'undefined') {
            gsap.set(img, { scale: 1 });
            floatTween = gsap.to(img, {
                scale: 1.025,
                duration: 3.5,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut'
            });
        }
    }

    function animateSlideIn(targetSlide, direction = 1) {
        const badge = targetSlide.querySelector('.hero-text .hero-badge-pill, .hero-text h4');
        const h1 = targetSlide.querySelector('.hero-text h1');
        const callout = targetSlide.querySelector('.hero-text .hero-sale-callout, .hero-text p.sale');
        const desc = targetSlide.querySelector('.hero-text p.desc');
        const btn = targetSlide.querySelector('.hero-text .hero-btn');
        const img = targetSlide.querySelector('.hero-image');
        targetSlide.style.display = 'block';
        targetSlide.classList.add('active');

        if (typeof gsap === 'undefined') {
            isAnimating = false;
            return;
        }

        const tl = gsap.timeline({
            onComplete: () => {
                isAnimating = false;
                startImageFloat(targetSlide);
            }
        });

        tl.fromTo(targetSlide, 
            { opacity: 0, scale: 0.98 }, 
            { opacity: 1, scale: 1, duration: 0.45, ease: 'power2.out' }
        );

        if (badge) {
            tl.fromTo(badge,
                { y: -25, opacity: 0 },
                { y: 0, opacity: 1, duration: 0.55, ease: 'back.out(1.7)' },
                '-=0.35'
            );
        }

        if (h1) {
            tl.fromTo(h1,
                { x: direction > 0 ? -40 : 40, opacity: 0, filter: 'blur(4px)' },
                { x: 0, opacity: 1, filter: 'blur(0px)', duration: 0.65, ease: 'power3.out' },
                '-=0.45'
            );
        }

        if (callout) {
            tl.fromTo(callout,
                { y: 15, opacity: 0 },
                { y: 0, opacity: 1, duration: 0.5, ease: 'power2.out' },
                '-=0.4'
            );
        }

        if (desc) {
            tl.fromTo(desc,
                { y: 15, opacity: 0 },
                { y: 0, opacity: 1, duration: 0.5, ease: 'power2.out' },
                '-=0.35'
            );
        }

        if (btn) {
            tl.fromTo(btn,
                { y: 20, opacity: 0, scale: 0.9 },
                { y: 0, opacity: 1, scale: 1, duration: 0.55, ease: 'back.out(2)' },
                '-=0.35'
            );
        }

        if (img) {
            tl.fromTo(img,
                { x: direction > 0 ? 25 : -25, opacity: 0, scale: 0.98 },
                { x: 0, opacity: 1, scale: 1, duration: 0.7, ease: 'power2.out' },
                '-=0.55'
            );
        }
    }

    // Animate first slide on load
    animateSlideIn(slides[0], 1);

    if (slides.length > 1) {
        function goToSlide(newIndex, direction = 1) {
            if (isAnimating || newIndex === currentSlide) return;
            isAnimating = true;

            const oldSlide = slides[currentSlide];
            const newSlide = slides[newIndex];

            if (dots[currentSlide]) dots[currentSlide].classList.remove('active');
            if (dots[newIndex]) dots[newIndex].classList.add('active');

            if (floatTween) floatTween.kill();

            if (typeof gsap === 'undefined') {
                oldSlide.style.display = 'none';
                oldSlide.classList.remove('active');
                newSlide.style.display = 'block';
                newSlide.classList.add('active');
                currentSlide = newIndex;
                isAnimating = false;
                return;
            }

            gsap.to(oldSlide, {
                opacity: 0,
                scale: 0.97,
                duration: 0.35,
                ease: 'power2.in',
                onComplete: () => {
                    oldSlide.style.display = 'none';
                    oldSlide.classList.remove('active');
                    currentSlide = newIndex;
                    animateSlideIn(newSlide, direction);
                }
            });
        }

        function nextSlide() {
            const nextIndex = (currentSlide + 1) % slides.length;
            goToSlide(nextIndex, 1);
        }

        function prevSlide() {
            const prevIndex = (currentSlide - 1 + slides.length) % slides.length;
            goToSlide(prevIndex, -1);
        }

        function startAutoplay() {
            stopAutoplay();
            slideInterval = setInterval(nextSlide, 5000);
        }

        function stopAutoplay() {
            clearInterval(slideInterval);
        }

        function manualSwitch(action) {
            stopAutoplay();
            action();
            startAutoplay();
        }

        if (prevBtn) prevBtn.addEventListener('click', () => manualSwitch(prevSlide));
        if (nextBtn) nextBtn.addEventListener('click', () => manualSwitch(nextSlide));

        dots.forEach(dot => {
            dot.addEventListener('click', function() {
                const targetSlide = parseInt(this.getAttribute('data-slide-to'));
                const dir = targetSlide > currentSlide ? 1 : -1;
                manualSwitch(() => goToSlide(targetSlide, dir));
            });
        });

        if (wrapper) {
            wrapper.addEventListener('mouseenter', stopAutoplay);
            wrapper.addEventListener('mouseleave', startAutoplay);

            wrapper.addEventListener('mousemove', function(e) {
                const activeImg = slides[currentSlide].querySelector('.hero-image img');
                if (activeImg && window.innerWidth > 768 && typeof gsap !== 'undefined') {
                    const rect = wrapper.getBoundingClientRect();
                    const mouseX = (e.clientX - rect.left) / rect.width - 0.5;
                    const mouseY = (e.clientY - rect.top) / rect.height - 0.5;
                    gsap.to(activeImg, {
                        x: mouseX * 20,
                        y: mouseY * 15,
                        rotation: mouseX * 3,
                        duration: 0.6,
                        ease: 'power2.out'
                    });
                }
            });

            let touchStartX = 0;
            wrapper.addEventListener('touchstart', (e) => touchStartX = e.changedTouches[0].screenX, { passive: true });
            wrapper.addEventListener('touchend', (e) => {
                const swipeDistance = e.changedTouches[0].screenX - touchStartX;
                if (Math.abs(swipeDistance) > 40) {
                    if (swipeDistance < 0) manualSwitch(nextSlide);
                    else manualSwitch(prevSlide);
                }
            }, { passive: true });
        }

        startAutoplay();
    }
    // ==========================================
    // Category Slider Navigation Logic
    // ==========================================
    const categoryGrid = document.querySelector('.category-grid');
    const catPrevBtn = document.querySelector('.category-nav-arrow.cat-prev');
    const catNextBtn = document.querySelector('.category-nav-arrow.cat-next');

    if (categoryGrid) {
        function updateCategoryArrows() {
            if (!catPrevBtn || !catNextBtn) return;
            const maxScroll = categoryGrid.scrollWidth - categoryGrid.clientWidth;
            
            // Show or hide navigation arrows based on overflow
            if (maxScroll <= 5) {
                catPrevBtn.style.display = 'none';
                catNextBtn.style.display = 'none';
            } else {
                catPrevBtn.style.display = 'flex';
                catNextBtn.style.display = 'flex';
                
                if (categoryGrid.scrollLeft <= 5) {
                    catPrevBtn.classList.add('disabled');
                } else {
                    catPrevBtn.classList.remove('disabled');
                }

                if (categoryGrid.scrollLeft >= maxScroll - 5) {
                    catNextBtn.classList.add('disabled');
                } else {
                    catNextBtn.classList.remove('disabled');
                }
            }
        }

        if (catPrevBtn) {
            catPrevBtn.addEventListener('click', function() {
                const firstCard = categoryGrid.querySelector('.category-card');
                const scrollStep = firstCard ? (firstCard.offsetWidth + 20) * 2 : 360;
                categoryGrid.scrollBy({ left: -scrollStep, behavior: 'smooth' });
            });
        }

        if (catNextBtn) {
            catNextBtn.addEventListener('click', function() {
                const firstCard = categoryGrid.querySelector('.category-card');
                const scrollStep = firstCard ? (firstCard.offsetWidth + 20) * 2 : 360;
                categoryGrid.scrollBy({ left: scrollStep, behavior: 'smooth' });
            });
        }

        categoryGrid.addEventListener('scroll', updateCategoryArrows);
        window.addEventListener('resize', updateCategoryArrows);
        updateCategoryArrows();
    }

    // ==========================================
    // Scroll Reveal Observer for Cards (Figma Motion Standards)
    // ==========================================
    const revealElements = document.querySelectorAll('.scroll-reveal, .safe-reveal');
    if (revealElements.length > 0) {
        // Enable animated state for browsers with active JS
        document.body.classList.add('js-animate');

        if ('IntersectionObserver' in window) {
            const revealObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.08,
                rootMargin: '0px 0px -30px 0px'
            });
            revealElements.forEach(el => revealObserver.observe(el));
        } else {
            revealElements.forEach(el => el.classList.add('is-visible'));
        }
    }
});
