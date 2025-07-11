<?php
require_once 'config/config.php';
require_once 'includes/header.php';

// Get page content from database
$db = getDBConnection();
$stmt = $db->prepare("SELECT * FROM page_contents WHERE page_slug = 'services' AND is_active = 1");
$stmt->execute();
$page = $stmt->fetch(PDO::FETCH_ASSOC);

// Get services from database
$services = $db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Services Hero Section -->
<section class="relative bg-gray-900 text-white py-32">
    <div class="absolute inset-0 bg-gradient-to-r from-red-600/20 to-blue-600/20"></div>
    <div class="container mx-auto px-4 relative">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6"><?php echo htmlspecialchars($page['title'] ?? 'Our Services'); ?></h1>
            <p class="text-xl text-gray-300 mb-8"><?php echo nl2br(htmlspecialchars($page['content'] ?? 'Explore our comprehensive range of technology services designed to help your business thrive in the digital age.')); ?></p>
        </div>
    </div>
</section>

<!-- Services Grid Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($services as $service): ?>
            <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300">
                <div class="text-red-600 mb-4">
                    <i class="<?php echo htmlspecialchars($service['icon']); ?> fa-3x"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3"><?php echo htmlspecialchars($service['title']); ?></h3>
                <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($service['description'])); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Process Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Our Process</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">How we work with you to deliver exceptional results.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="text-red-600 mb-4">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Discovery</h3>
                <p class="text-gray-600">We begin by understanding your needs, goals, and requirements.</p>
            </div>
            
            <div class="text-center">
                <div class="text-red-600 mb-4">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Planning</h3>
                <p class="text-gray-600">We develop a detailed plan tailored to your specific needs.</p>
            </div>
            
            <div class="text-center">
                <div class="text-red-600 mb-4">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Development</h3>
                <p class="text-gray-600">We build your solution using the latest technologies and best practices.</p>
            </div>
            
            <div class="text-center">
                <div class="text-red-600 mb-4">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Delivery</h3>
                <p class="text-gray-600">We ensure smooth deployment and provide ongoing support.</p>
            </div>
        </div>
    </div>
</section>

<!-- Client Testimonials Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16 animate__animated animate__slideInDown">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">What Our Clients Say</h2>
            <p class="text-xl text-gray-600">Hear from businesses that have transformed with our services</p>
        </div>
        
        <!-- Testimonial Slider -->
        <div class="testimonial-slider relative overflow-hidden">
            <div class="testimonial-track flex transition-transform duration-500 ease-in-out">
                <?php
                // Get testimonials from database
                try {
                    $testimonialsStmt = $db->query("SELECT * FROM testimonials WHERE status = 'approved' ORDER BY created_at DESC LIMIT 9");
                    if ($testimonialsStmt !== false) {
                        $testimonials = $testimonialsStmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($testimonials as $testimonial): ?>
                            <div class="testimonial-slide flex-shrink-0 w-full md:w-1/2 lg:w-1/3 px-4">
                                <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 h-full hover:shadow-xl transition duration-300">
                                    <div class="flex flex-col sm:flex-row items-center mb-4">
                                        <?php if (!empty($testimonial['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($testimonial['image_url']); ?>" alt="<?php echo htmlspecialchars($testimonial['client_name']); ?>" class="w-16 h-16 rounded-full object-cover mb-3 sm:mb-0 sm:mr-4">
                                        <?php else: ?>
                                            <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center mb-3 sm:mb-0 sm:mr-4">
                                                <span class="text-2xl font-bold text-gray-500"><?php echo substr(htmlspecialchars($testimonial['client_name']), 0, 1); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="text-center sm:text-left">
                                            <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($testimonial['client_name']); ?></h3>
                                            <p class="text-gray-600 text-sm">
                                                <?php if (!empty($testimonial['client_position'])): ?>
                                                    <?php echo htmlspecialchars($testimonial['client_position']); ?>
                                                <?php endif; ?>
                                                <?php if (!empty($testimonial['company_name'])): ?>
                                                    <?php echo !empty($testimonial['client_position']) ? ', ' : ''; ?>
                                                    <?php echo htmlspecialchars($testimonial['company_name']); ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mb-4 text-yellow-400 text-center sm:text-left">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $testimonial['rating']): ?>
                                                <i class="fas fa-star"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="text-gray-700 italic text-sm sm:text-base">"<?php echo htmlspecialchars($testimonial['testimonial']); ?>"</p>
                                </div>
                            </div>
                        <?php endforeach;
                    }
                } catch (Exception $e) {
                    error_log("Error fetching testimonials: " . $e->getMessage());
                }
                ?>
            </div>
            
            <!-- Navigation Buttons -->
            <button class="testimonial-prev absolute top-1/2 left-1 sm:left-0 transform -translate-y-1/2 bg-red-600 text-white rounded-full w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center shadow-lg z-10 focus:outline-none hover:bg-red-700 transition">
                <i class="fas fa-chevron-left text-sm sm:text-base"></i>
            </button>
            <button class="testimonial-next absolute top-1/2 right-1 sm:right-0 transform -translate-y-1/2 bg-red-600 text-white rounded-full w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center shadow-lg z-10 focus:outline-none hover:bg-red-700 transition">
                <i class="fas fa-chevron-right text-sm sm:text-base"></i>
            </button>
            
            <!-- Dots Navigation -->
            <div class="testimonial-dots flex justify-center mt-6 space-x-2"></div>
        </div>
    </div>
</section>

<!-- Add this JavaScript before the closing body tag or in your footer -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const track = document.querySelector('.testimonial-track');
    const slides = document.querySelectorAll('.testimonial-slide');
    const nextButton = document.querySelector('.testimonial-next');
    const prevButton = document.querySelector('.testimonial-prev');
    const dotsContainer = document.querySelector('.testimonial-dots');
    
    if (!track || slides.length === 0) return;
    
    let slideWidth = slides[0].getBoundingClientRect().width;
    let slideIndex = 0;
    let slidesPerView = 1;
    let touchStartX = 0;
    let touchEndX = 0;
    
    // Determine slides per view based on screen size
    function updateSlidesPerView() {
        if (window.innerWidth >= 1024) {
            slidesPerView = 3; // Large screens
        } else if (window.innerWidth >= 768) {
            slidesPerView = 2; // Medium screens
        } else {
            slidesPerView = 1; // Small screens
        }
        
        // Update slide width
        slideWidth = track.clientWidth / slidesPerView;
        slides.forEach(slide => {
            slide.style.width = `${slideWidth}px`;
        });
        
        // Update track position
        goToSlide(slideIndex);
        
        // Create dots
        createDots();
    }
    
    // Create navigation dots
    function createDots() {
        dotsContainer.innerHTML = '';
        const numDots = Math.ceil(slides.length / slidesPerView);
        
        for (let i = 0; i < numDots; i++) {
            const dot = document.createElement('button');
            dot.classList.add('w-3', 'h-3', 'rounded-full', 'bg-gray-300', 'hover:bg-red-600', 'transition');
            if (Math.floor(slideIndex / slidesPerView) === i) {
                dot.classList.add('bg-red-600');
            }
            
            dot.addEventListener('click', () => {
                goToSlide(i * slidesPerView);
            });
            
            dotsContainer.appendChild(dot);
        }
    }
    
    // Go to specific slide
    function goToSlide(index) {
        // Ensure index is within bounds
        if (index < 0) {
            slideIndex = 0;
        } else if (index > slides.length - slidesPerView) {
            slideIndex = slides.length - slidesPerView;
        } else {
            slideIndex = index;
        }
        
        // Move track
        track.style.transform = `translateX(-${slideIndex * slideWidth}px)`;
        
        // Update dots
        updateDots();
    }
    
    // Update active dot
    function updateDots() {
        const dots = dotsContainer.querySelectorAll('button');
        const activeDotIndex = Math.floor(slideIndex / slidesPerView);
        
        dots.forEach((dot, index) => {
            if (index === activeDotIndex) {
                dot.classList.add('bg-red-600');
                dot.classList.remove('bg-gray-300');
            } else {
                dot.classList.remove('bg-red-600');
                dot.classList.add('bg-gray-300');
            }
        });
    }
    
    // Next slide
    function nextSlide() {
        goToSlide(slideIndex + slidesPerView);
    }
    
    // Previous slide
    function prevSlide() {
        goToSlide(slideIndex - slidesPerView);
    }
    
    // Auto slide
    let autoSlideInterval;
    function startAutoSlide() {
        autoSlideInterval = setInterval(() => {
            if (slideIndex >= slides.length - slidesPerView) {
                goToSlide(0); // Loop back to first slide
            } else {
                nextSlide();
            }
        }, 5000); // Change slide every 5 seconds
    }
    
    function stopAutoSlide() {
        clearInterval(autoSlideInterval);
    }
    
    // Touch events for mobile swipe
    track.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        stopAutoSlide();
    });
    
    track.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
        startAutoSlide();
    });
    
    function handleSwipe() {
        const swipeThreshold = 50; // Minimum distance to register as swipe
        if (touchEndX < touchStartX - swipeThreshold) {
            // Swipe left - go to next slide
            nextSlide();
        } else if (touchEndX > touchStartX + swipeThreshold) {
            // Swipe right - go to previous slide
            prevSlide();
        }
    }
    
    // Event listeners
    nextButton.addEventListener('click', () => {
        nextSlide();
        stopAutoSlide();
        startAutoSlide();
    });
    
    prevButton.addEventListener('click', () => {
        prevSlide();
        stopAutoSlide();
        startAutoSlide();
    });
    
    // Handle window resize
    window.addEventListener('resize', () => {
        updateSlidesPerView();
    });
    
    // Initialize slider
    updateSlidesPerView();
    startAutoSlide();
    
    // Pause auto-slide on hover
    track.addEventListener('mouseenter', stopAutoSlide);
    track.addEventListener('mouseleave', startAutoSlide);
});
</script>

<?php require_once 'includes/footer.php'; ?>