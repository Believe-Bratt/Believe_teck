// Smooth Scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Intersection Observer for Animations
const animateOnScroll = () => {
    const elements = document.querySelectorAll('.animate-on-scroll');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    elements.forEach(element => observer.observe(element));
};

// Mobile Menu Animation
const mobileMenu = document.getElementById('mobile-menu');
const mobileMenuButton = document.getElementById('mobile-menu-button');

if (mobileMenuButton && mobileMenu) {
    let isMenuOpen = false;
    
    mobileMenuButton.addEventListener('click', () => {
        isMenuOpen = !isMenuOpen;
        mobileMenu.classList.toggle('hidden');
        
        // Add transition classes
        if (isMenuOpen) {
            mobileMenu.classList.add('opacity-100', 'translate-y-0');
            mobileMenu.classList.remove('opacity-0', '-translate-y-4');
            document.body.style.overflow = 'hidden'; // Prevent scrolling when menu is open
        } else {
            mobileMenu.classList.add('opacity-0', '-translate-y-4');
            mobileMenu.classList.remove('opacity-100', 'translate-y-0');
            document.body.style.overflow = ''; // Restore scrolling
        }
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (isMenuOpen && !mobileMenu.contains(e.target) && !mobileMenuButton.contains(e.target)) {
            mobileMenuButton.click();
        }
    });
}

// Improved Form Validation with Better Feedback
const validateForm = (formId) => {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        let isValid = true;
        const inputs = form.querySelectorAll('input, textarea');
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // Clear previous error messages
        form.querySelectorAll('.error-message').forEach(el => el.remove());
        inputs.forEach(input => input.classList.remove('border-red-500'));

        // Validate inputs
        inputs.forEach(input => {
            if (input.hasAttribute('required') && !input.value.trim()) {
                isValid = false;
                input.classList.add('border-red-500');
                const errorMessage = document.createElement('p');
                errorMessage.className = 'error-message text-red-500 text-sm mt-1';
                errorMessage.textContent = 'This field is required';
                input.parentNode.appendChild(errorMessage);
            }
        });

        if (isValid) {
            try {
                // Add loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <div class="flex items-center justify-center">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                        <span class="ml-2">Submitting...</span>
                    </div>
                `;

                // Simulate form submission (replace with actual AJAX call)
                await new Promise(resolve => setTimeout(resolve, 2000));

                // Success state
                submitBtn.innerHTML = `
                    <div class="flex items-center justify-center text-green-500">
                        <i class="fas fa-check mr-2"></i>
                        Submitted Successfully
                    </div>
                `;
                form.reset();

                // Reset button after 3 seconds
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 3000);
            } catch (error) {
                // Error state
                submitBtn.innerHTML = `
                    <div class="flex items-center justify-center text-red-500">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        Error Submitting
                    </div>
                `;
                console.error('Form submission error:', error);
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 3000);
            }
        }
    });
};

// Testimonial Carousel
const initTestimonialCarousel = () => {
    const carousel = document.querySelector('.testimonial-carousel');
    if (!carousel) return;

    let currentSlide = 0;
    const slides = carousel.querySelectorAll('.testimonial-slide');
    const totalSlides = slides.length;

    const showSlide = (index) => {
        slides.forEach((slide, i) => {
            slide.style.transform = `translateX(${100 * (i - index)}%)`;
        });
    };

    const nextSlide = () => {
        currentSlide = (currentSlide + 1) % totalSlides;
        showSlide(currentSlide);
    };

    // Auto advance slides
    setInterval(nextSlide, 5000);
};

// Initialize all features when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    animateOnScroll();
    validateForm('contactForm');
    initTestimonialCarousel();
});

// Parallax Effect
window.addEventListener('scroll', () => {
    const parallaxElements = document.querySelectorAll('.parallax');
    parallaxElements.forEach(element => {
        const speed = element.dataset.speed || 0.5;
        const yPos = -(window.pageYOffset * speed);
        element.style.transform = `translateY(${yPos}px)`;
    });
}); 