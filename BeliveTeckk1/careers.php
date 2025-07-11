<?php
require_once  __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Database.php';
require_once  __DIR__ . '/classes/Job.php';

$db = Database::getInstance();
$job = new Job($db);

// Get all active jobs
$jobs = $job->getAllActiveJobs();

// Set page title for header
$page_title = "Careers - Believe Teckk";
$active_page = "careers";

// Include header
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="bg-gradient text-white py-16">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-6 animate-fade-in">Join Our Team</h1>
        <p class="text-xl animate-fade-in">Build your career with a company that values innovation and growth</p>
    </div>
</section>

<!-- Why Join Us -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-center mb-12 animate-on-scroll">Why Join Believe Teckk?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="card p-6 text-center hover-scale animate-on-scroll">
                <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-rocket text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-4">Innovation First</h3>
                <p class="text-gray-600">Work with cutting-edge technologies and contribute to innovative solutions that make a difference.</p>
            </div>
            <div class="card p-6 text-center hover-scale animate-on-scroll">
                <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-4">Great Culture</h3>
                <p class="text-gray-600">Join a diverse and inclusive team that values collaboration, creativity, and personal growth.</p>
            </div>
            <div class="card p-6 text-center hover-scale animate-on-scroll">
                <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chart-line text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-4">Growth Opportunities</h3>
                <p class="text-gray-600">Develop your skills and advance your career through continuous learning and mentorship.</p>
            </div>
        </div>
    </div>
</section>

<!-- Current Openings -->
<section class="py-16 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-center mb-12 animate-on-scroll">Current Openings</h2>
        
        <!-- Job Listings -->
        <div class="space-y-6">
            <?php if (empty($jobs)): ?>
                <div class="text-center text-gray-600">
                    <p>No job openings at the moment. Please check back later.</p>
                </div>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <div class="card p-6 hover-scale animate-on-scroll">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                            <div>
                                <h3 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($job['title']); ?></h3>
                                <p class="text-gray-600 mb-4">
                                    <?php echo htmlspecialchars($job['type']); ?> · 
                                    <?php echo htmlspecialchars($job['location']); ?>
                                </p>
                                <?php if ($job['salary_range']): ?>
                                    <p class="text-gray-600 mb-4">Salary: <?php echo htmlspecialchars($job['salary_range']); ?></p>
                                <?php endif; ?>
                                <div class="mb-4 md:mb-0">
                                    <?php if ($job['requirements']): ?>
                                        <h4 class="font-semibold mb-2">Requirements:</h4>
                                        <ul class="space-y-2">
                                            <?php foreach (explode("\n", $job['requirements']) as $req): ?>
                                                <li class="flex items-center">
                                                    <i class="fas fa-check-circle text-red-500 mr-2"></i>
                                                    <?php echo htmlspecialchars(trim($req)); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="apply.php?id=<?php echo $job['id']; ?>" 
                               class="btn-primary mt-4 md:mt-0">
                                Apply Now
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Benefits -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-center mb-12 animate-on-scroll">Benefits & Perks</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="card p-6 text-center hover-scale animate-on-scroll">
                <i class="fas fa-heart text-4xl text-red-500 mb-4"></i>
                <h3 class="text-xl font-bold mb-4">Health Insurance</h3>
                <p class="text-gray-600">Comprehensive health coverage for you and your family</p>
            </div>
            <div class="card p-6 text-center hover-scale animate-on-scroll">
                <i class="fas fa-graduation-cap text-4xl text-blue-500 mb-4"></i>
                <h3 class="text-xl font-bold mb-4">Learning Budget</h3>
                <p class="text-gray-600">Annual budget for courses and certifications</p>
            </div>
            <div class="card p-6 text-center hover-scale animate-on-scroll">
                <i class="fas fa-home text-4xl text-red-500 mb-4"></i>
                <h3 class="text-xl font-bold mb-4">Remote Work</h3>
                <p class="text-gray-600">Flexible work arrangements and remote options</p>
            </div>
            <div class="card p-6 text-center hover-scale animate-on-scroll">
                <i class="fas fa-gift text-4xl text-blue-500 mb-4"></i>
                <h3 class="text-xl font-bold mb-4">Bonus Program</h3>
                <p class="text-gray-600">Performance-based bonuses and incentives</p>
            </div>
        </div>
    </div>
</section>

<!-- Company Culture -->
<section class="py-16 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-center mb-12 animate-on-scroll">Our Culture</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <div class="animate-slide-left">
                <img src="assets/images/culture1.jpg" alt="Company Culture" class="rounded-lg shadow-xl mb-6">
                <img src="assets/images/culture2.jpg" alt="Company Culture" class="rounded-lg shadow-xl">
            </div>
            <div class="space-y-6 animate-slide-right">
                <div class="card p-6">
                    <h3 class="text-2xl font-bold mb-4">Innovation & Creativity</h3>
                    <p class="text-gray-600">We encourage creative thinking and innovative solutions to complex problems.</p>
                </div>
                <div class="card p-6">
                    <h3 class="text-2xl font-bold mb-4">Work-Life Balance</h3>
                    <p class="text-gray-600">We believe in maintaining a healthy balance between work and personal life.</p>
                </div>
                <div class="card p-6">
                    <h3 class="text-2xl font-bold mb-4">Continuous Learning</h3>
                    <p class="text-gray-600">We support professional development and skill enhancement.</p>
                </div>
                <div class="card p-6">
                    <h3 class="text-2xl font-bold mb-4">Team Collaboration</h3>
                    <p class="text-gray-600">We foster a collaborative environment where everyone's voice is heard.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Application Process -->
<section class="py-16 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-center mb-12 animate-on-scroll">Application Process</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="card p-6 text-center hover-scale animate-on-scroll">
                <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">1</div>
                <h3 class="text-xl font-bold mb-4">Apply Online</h3>
                <p class="text-gray-600">Submit your application with your resume and portfolio</p>
            </div>
            <div class="card p-6 text-center hover-scale animate-on-scroll">
                <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">2</div>
                <h3 class="text-xl font-bold mb-4">Initial Review</h3>
                <p class="text-gray-600">Our team reviews your application and credentials</p>
            </div>
            <div class="card p-6 text-center hover-scale animate-on-scroll">
                <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">3</div>
                <h3 class="text-xl font-bold mb-4">Interviews</h3>
                <p class="text-gray-600">Technical and cultural fit interviews</p>
            </div>
            <div class="card p-6 text-center hover-scale animate-on-scroll">
                <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">4</div>
                <h3 class="text-xl font-bold mb-4">Offer</h3>
                <p class="text-gray-600">Welcome to the Believe Teckk family!</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="bg-gradient text-white py-16">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold mb-6 animate-on-scroll">Ready to Join Our Team?</h2>
        <p class="text-xl mb-8 animate-on-scroll">Take the first step towards an exciting career with Believe Teckk</p>
        <div class="flex justify-center space-x-4">
            <a href="#current-openings" class="btn-primary animate-on-scroll">View All Positions</a>
            <a href="contact.php" class="bg-white text-black px-6 py-3 rounded-lg hover:bg-gray-100 transition duration-300 animate-on-scroll">Contact HR</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?> 