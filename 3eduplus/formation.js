// Filtering functionality with Pagination
document.addEventListener('DOMContentLoaded', function() {
    const courseGrid = document.getElementById('courseGrid');
    const courseCards = document.querySelectorAll('.course-card');
    const searchInput = document.getElementById('searchInput');
    const categoryCheckboxes = document.querySelectorAll('input[name="category"]');
    const levelCheckboxes = document.querySelectorAll('input[name="level"]');
    const priceCheckboxes = document.querySelectorAll('input[name="price"]');
    const sortSelect = document.getElementById('sortSelect');
    const courseCount = document.querySelector('.course-count');
    
    // Pagination elements
    const paginationContainer = document.querySelector('.pagination');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    const pageNumberBtns = document.querySelectorAll('.pagination-number');
    
    // Pagination variables
    const itemsPerPage = 6;
    let currentPage = 1;
    let filteredCards = Array.from(courseCards);
    let totalPages = Math.ceil(filteredCards.length / itemsPerPage);
    
    // Initialize
    updatePagination();
    updateCourseCount();
    displayCurrentPage();
    
    // Function to display current page courses
    function displayCurrentPage() {
        // Hide all cards first
        courseCards.forEach(card => card.style.display = 'none');
        
        // Calculate start and end index
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        
        // Show only cards for current page
        const currentCards = filteredCards.slice(startIndex, endIndex);
        currentCards.forEach(card => card.style.display = 'block');
    }
    
    // Function to update pagination controls
    function updatePagination() {
        totalPages = Math.ceil(filteredCards.length / itemsPerPage);
        
        // Hide pagination if only one page
        if (totalPages <= 1) {
            paginationContainer.style.display = 'none';
            return;
        }
        
        paginationContainer.style.display = 'flex';
        
        // Update previous button
        if (prevBtn) {
            if (currentPage === 1) {
                prevBtn.classList.add('disabled');
                prevBtn.disabled = true;
            } else {
                prevBtn.classList.remove('disabled');
                prevBtn.disabled = false;
            }
        }
        
        // Update next button
        if (nextBtn) {
            if (currentPage === totalPages) {
                nextBtn.classList.add('disabled');
                nextBtn.disabled = true;
            } else {
                nextBtn.classList.remove('disabled');
                nextBtn.disabled = false;
            }
        }
        
        // Update page numbers - simple version for 1,2,3
        pageNumberBtns.forEach((btn, index) => {
            const pageNum = index + 1;
            
            // Show only up to total pages
            if (pageNum <= totalPages) {
                btn.style.display = 'inline-flex';
                btn.textContent = pageNum;
                
                if (pageNum === currentPage) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            } else {
                btn.style.display = 'none';
            }
        });
    }
    
    // Function to go to specific page
    function goToPage(page) {
        if (page < 1 || page > totalPages) return;
        
        currentPage = page;
        displayCurrentPage();
        updatePagination();
        updateCourseCount();
        
        // Smooth scroll to top of courses
        window.scrollTo({
            top: courseGrid.offsetTop - 100,
            behavior: 'smooth'
        });
    }
    
    // Function to update course count
    function updateCourseCount() {
        const startIndex = (currentPage - 1) * itemsPerPage + 1;
        const endIndex = Math.min(currentPage * itemsPerPage, filteredCards.length);
        courseCount.textContent = `Affichage ${startIndex}-${endIndex} sur ${filteredCards.length} formations`;
    }
    
    // Function to filter courses
    function filterCourses() {
        const searchTerm = searchInput.value.toLowerCase();
        
        // Get selected categories
        const selectedCategories = [];
        categoryCheckboxes.forEach(cb => {
            if (cb.checked && cb.value !== 'all') {
                selectedCategories.push(cb.value);
            }
        });
        
        // Get selected levels
        const selectedLevels = [];
        levelCheckboxes.forEach(cb => {
            if (cb.checked && cb.value !== 'all') {
                selectedLevels.push(cb.value);
            }
        });
        
        // Get selected price ranges
        const selectedPrices = [];
        priceCheckboxes.forEach(cb => {
            if (cb.checked) {
                selectedPrices.push(cb.value);
            }
        });
        
        // Filter cards
        filteredCards = Array.from(courseCards).filter(card => {
            let showCard = true;
            
            // Search filter
            if (searchTerm) {
                const courseTitle = card.querySelector('.course-title').textContent.toLowerCase();
                const courseDescription = card.querySelector('.course-description').textContent.toLowerCase();
                if (!courseTitle.includes(searchTerm) && !courseDescription.includes(searchTerm)) {
                    showCard = false;
                }
            }
            
            // Category filter
            if (selectedCategories.length > 0 && showCard) {
                const courseCategory = card.dataset.category.toLowerCase();
                const matchesCategory = selectedCategories.some(cat => {
                    const categoryMap = {
                        'web-dev': 'développement web',
                        'design': 'design',
                        'marketing': 'marketing',
                        'data': 'data science'
                    };
                    return courseCategory.includes(categoryMap[cat] || cat);
                });
                if (!matchesCategory) showCard = false;
            }
            
            // Level filter
            if (selectedLevels.length > 0 && showCard) {
                const courseLevel = card.dataset.level.toLowerCase();
                const matchesLevel = selectedLevels.some(level => {
                    const levelMap = {
                        'beginner': 'débutant',
                        'intermediate': 'intermédiaire',
                        'advanced': 'avancé'
                    };
                    return courseLevel.includes(levelMap[level] || level);
                });
                if (!matchesLevel) showCard = false;
            }
            
            // Price filter
            if (selectedPrices.length > 0 && showCard) {
                const coursePrice = parseFloat(card.dataset.price);
                let matchesPrice = false;
                
                selectedPrices.forEach(priceRange => {
                    switch(priceRange) {
                        case 'under-300':
                            if (coursePrice < 300) matchesPrice = true;
                            break;
                        case '300-500':
                            if (coursePrice >= 300 && coursePrice <= 500) matchesPrice = true;
                            break;
                        case 'over-500':
                            if (coursePrice > 500) matchesPrice = true;
                            break;
                    }
                });
                
                if (!matchesPrice) showCard = false;
            }
            
            return showCard;
        });
        
        // Reset to page 1 when filters change
        currentPage = 1;
        displayCurrentPage();
        updatePagination();
        updateCourseCount();
    }
    
    // Function to sort courses
    function sortCourses() {
        const sortValue = sortSelect.value;
        
        // Sort filtered cards
        filteredCards.sort((a, b) => {
            switch(sortValue) {
                case 'price-low':
                    return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                case 'price-high':
                    return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                case 'rating':
                    return (parseFloat(b.dataset.rating) || 0) - (parseFloat(a.dataset.rating) || 0);
                case 'popular':
                default:
                    return 0; // Keep original order for popular
            }
        });
        
        // Re-sort all cards in the DOM to maintain visual order
        const sortedCards = [...filteredCards];
        const allCards = Array.from(courseCards);
        
        // Get the order of sorted cards
        sortedCards.forEach((sortedCard, index) => {
            const originalIndex = allCards.indexOf(sortedCard);
            if (originalIndex > -1) {
                // Move card to correct position in DOM
                if (index < allCards.length) {
                    courseGrid.insertBefore(sortedCard, allCards[index] || null);
                }
            }
        });
        
        // Update display
        displayCurrentPage();
    }
    
    // Event listeners for pagination
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                goToPage(currentPage - 1);
            }
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            if (currentPage < totalPages) {
                goToPage(currentPage + 1);
            }
        });
    }
    
    pageNumberBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const pageNum = parseInt(this.textContent);
            if (!isNaN(pageNum)) {
                goToPage(pageNum);
            }
        });
    });
    
    // Event listeners for filters
    searchInput.addEventListener('input', filterCourses);
    
    categoryCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.value === 'all' && this.checked) {
                categoryCheckboxes.forEach(c => {
                    if (c.value !== 'all') c.checked = false;
                });
            } else if (this.value !== 'all' && this.checked) {
                const allCheckbox = document.querySelector('input[name="category"][value="all"]');
                if (allCheckbox) allCheckbox.checked = false;
            }
            filterCourses();
        });
    });
    
    levelCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.value === 'all' && this.checked) {
                levelCheckboxes.forEach(c => {
                    if (c.value !== 'all') c.checked = false;
                });
            } else if (this.value !== 'all' && this.checked) {
                const allCheckbox = document.querySelector('input[name="level"][value="all"]');
                if (allCheckbox) allCheckbox.checked = false;
            }
            filterCourses();
        });
    });
    
    priceCheckboxes.forEach(cb => {
        cb.addEventListener('change', filterCourses);
    });
    
    sortSelect.addEventListener('change', sortCourses);
    
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function() {
            const cartCount = document.querySelector('.cart-count');
            let count = parseInt(cartCount.textContent) || 0;
            count++;
            cartCount.textContent = count;
            
            // Show confirmation
            this.innerHTML = '<i class="fas fa-check"></i> Ajouté';
            this.classList.add('added');
            
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-shopping-cart"></i> Ajouter';
                this.classList.remove('added');
            }, 2000);
        });
    });
    
    // Dark mode toggle
    const darkModeBtn = document.querySelector('.darkMode');
    if (darkModeBtn) {
        darkModeBtn.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            const icon = this.querySelector('i');
            if (document.body.classList.contains('dark-mode')) {
                icon.className = 'fas fa-sun';
                icon.style.color = 'orange';
            } else {
                icon.className = 'fas fa-moon';
                icon.style.color = 'rgba(245, 196, 0, 0.873)';
            }
        });
    }
    
    // Add CSS for existing pagination
    const paginationStyles = `
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 30px;
        padding: 20px 0;
    }
    
    .pagination-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        color: #495057;
        transition: all 0.3s ease;
    }
    
    .pagination-btn:hover:not(.disabled) {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    .pagination-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f8f9fa;
        color: #6c757d;
    }
    
    .pagination-numbers {
        display: flex;
        gap: 5px;
        align-items: center;
    }
    
    .pagination-number {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background: white;
        cursor: pointer;
        font-size: 14px;
        color: #495057;
        transition: all 0.3s ease;
    }
    
    .pagination-number:hover:not(.active) {
        background: #f8f9fa;
        border-color: #007bff;
        color: #007bff;
    }
    
    .pagination-number.active {
        background: #007bff;
        color: white;
        border-color: #007bff;
        font-weight: bold;
    }
    
    @media (max-width: 768px) {
        .pagination {
            gap: 5px;
        }
        
        .pagination-btn {
            padding: 6px 12px;
            font-size: 13px;
        }
        
        .pagination-number {
            width: 35px;
            height: 35px;
            font-size: 13px;
        }
    }
    `;
    
    // Add dark mode styles
    const darkModeStyles = `
    .dark-mode {
        background-color: #1a1a1a;
        color: #ffffff;
    }
    
    .dark-mode .header-nav {
        background-color: #2d2d2d;
    }
    
    .dark-mode .sidebar {
        background-color: #2d2d2d;
    }
    
    .dark-mode .course-card {
        background-color: #2d2d2d;
        color: #ffffff;
    }
    
    .dark-mode .footer {
        background-color: #2d2d2d;
    }
    
    .dark-mode .pagination-btn:not(.disabled) {
        background-color: #2d2d2d;
        border-color: #444;
        color: #fff;
    }
    
    .dark-mode .pagination-number:not(.active) {
        background-color: #2d2d2d;
        border-color: #444;
        color: #fff;
    }
    
    .dark-mode .pagination-number:hover:not(.active) {
        background-color: #007bff;
        color: white;
    }
    `;
    
    const styleSheet = document.createElement("style");
    styleSheet.textContent = paginationStyles + darkModeStyles;
    document.head.appendChild(styleSheet);
});