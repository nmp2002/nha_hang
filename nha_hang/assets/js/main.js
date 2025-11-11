// Main JavaScript - Tối ưu và gọn gàng
(function() {
    'use strict';
    
    // Wait for DOM ready
    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }
    
    ready(function() {
        // Mobile menu toggle
        const navToggle = document.querySelector('.nav-toggle');
        const navMenu = document.querySelector('.nav-menu');
        if (navToggle && navMenu) {
            navToggle.addEventListener('click', function() {
                navMenu.classList.toggle('active');
            });
        }
        
        // Dropdown menu toggle (click instead of hover)
        document.querySelectorAll('.nav-dropdown > a').forEach(function(dropdownLink) {
            dropdownLink.addEventListener('click', function(e) {
                e.preventDefault();
                const dropdown = this.parentElement;
                const isActive = dropdown.classList.contains('active');
                
                // Đóng tất cả dropdown khác
                document.querySelectorAll('.nav-dropdown').forEach(function(dd) {
                    dd.classList.remove('active');
                });
                
                // Toggle dropdown hiện tại
                if (!isActive) {
                    dropdown.classList.add('active');
                }
            });
        });
        
        // Đóng dropdown khi click ra ngoài
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.nav-dropdown')) {
                document.querySelectorAll('.nav-dropdown').forEach(function(dropdown) {
                    dropdown.classList.remove('active');
                });
            }
        });
        
        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            }, 5000);
        });
        
        // Form validation
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(function(field) {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.style.borderColor = '#dc3545';
                        setTimeout(function() {
                            field.style.borderColor = '';
                        }, 3000);
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Vui lòng điền đầy đủ thông tin bắt buộc');
                }
            });
        });
        
        // Quantity input validation
        document.querySelectorAll('.qty-input').forEach(function(input) {
            input.addEventListener('change', function() {
                if (this.value < 1) this.value = 1;
            });
        });
        
        // Reservation date min
        const reservationDate = document.getElementById('reservation_date');
        if (reservationDate) {
            reservationDate.setAttribute('min', new Date().toISOString().split('T')[0]);
        }
        
        // Table capacity check
        const tableSelect = document.getElementById('table_id');
        const guestsInput = document.getElementById('number_of_guests');
        if (tableSelect && guestsInput) {
            tableSelect.addEventListener('change', function() {
                const capacity = this.options[this.selectedIndex].getAttribute('data-capacity');
                if (capacity) {
                    guestsInput.setAttribute('max', capacity);
                    if (parseInt(guestsInput.value) > parseInt(capacity)) {
                        guestsInput.value = capacity;
                    }
                }
            });
        }
        
        // Navbar scroll effect - đơn giản hóa
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            function updateNavbar() {
                navbar.classList.toggle('scrolled', window.scrollY > 50);
            }
            window.addEventListener('scroll', updateNavbar);
            updateNavbar(); // Check initial state
        }
        
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Dishes Carousel Auto-scroll (Horizontal)
        const carousel = document.getElementById('dishesCarousel');
        const dots = document.querySelectorAll('.carousel-dot');
        
        // Debug: Kiểm tra xem carousel có tồn tại không
        if (!carousel) {
            console.error('Carousel element not found!');
        }
        
        if (carousel && dots.length) {
            let currentIndex = 0;
            let autoScrollInterval;
            const totalItems = dots.length;
            let cardWidth = 0;
            
            function calculateCardWidth() {
                const firstCard = carousel.querySelector('.dish-card');
                if (firstCard) {
                    const style = window.getComputedStyle(firstCard);
                    const margin = parseFloat(style.marginRight) || 0;
                    cardWidth = firstCard.offsetWidth + 20; // 20px là gap trong CSS
                }
                return cardWidth;
            }
            
            function updateCarousel(index) {
                calculateCardWidth();
                // Scroll từng món một, hiển thị 3 món cùng lúc
                const scrollAmount = index * cardWidth;
                carousel.style.transform = `translateX(-${scrollAmount}px)`;
                
                // Cập nhật dot active dựa trên index hiện tại
                const dotIndex = Math.min(index, dots.length - 1);
                dots.forEach(function(dot, i) {
                    dot.classList.toggle('active', i === dotIndex);
                });
                currentIndex = index;
            }
            
            function nextSlide() {
                // Scroll món tiếp theo
                const nextIndex = (currentIndex + 1);
                // Nếu đã scroll hết, quay về đầu
                if (nextIndex >= totalItems - 2) { // -2 vì hiển thị 3 món cùng lúc
                    updateCarousel(0);
                } else {
                    updateCarousel(nextIndex);
                }
            }
            
            function startAutoScroll() {
                if (!autoScrollInterval) {
                    autoScrollInterval = setInterval(nextSlide, 2000);
                }
            }
            
            function stopAutoScroll() {
                if (autoScrollInterval) {
                    clearInterval(autoScrollInterval);
                    autoScrollInterval = null;
                }
            }
            
            dots.forEach(function(dot, index) {
                dot.addEventListener('click', function() {
                    stopAutoScroll();
                    updateCarousel(index);
                    startAutoScroll();
                });
            });
            
            const carouselWrapper = carousel.closest('.dishes-carousel-wrapper');
            if (carouselWrapper) {
                carouselWrapper.addEventListener('mouseenter', stopAutoScroll);
                carouselWrapper.addEventListener('mouseleave', startAutoScroll);
            }
            
            // Khởi động carousel sau khi DOM và images đã load
            function initCarousel() {
                // Đảm bảo carousel được set đúng CSS
                carousel.style.display = 'flex';
                carousel.style.flexDirection = 'row';
                
                calculateCardWidth();
                console.log('Carousel initialized. Card width:', cardWidth, 'Total items:', totalItems);
                
                if (cardWidth > 0) {
                    updateCarousel(0);
                    startAutoScroll();
                } else {
                    // Thử lại sau 200ms nếu chưa tính được width
                    setTimeout(initCarousel, 200);
                }
            }
            
            // Đợi DOM và images load
            if (document.readyState === 'complete') {
                setTimeout(initCarousel, 100);
            } else {
                window.addEventListener('load', function() {
                    setTimeout(initCarousel, 100);
                });
            }
        } else {
            console.warn('Carousel or dots not found. Carousel:', carousel, 'Dots:', dots.length);
        }
    });
})();

// Change quantity function (global function cho HTML inline)
function changeQty(btn, delta) {
    const input = btn.parentElement.querySelector('.qty-input');
    if (input) {
        const currentVal = parseInt(input.value) || 1;
        input.value = Math.max(1, currentVal + delta);
    }
}
