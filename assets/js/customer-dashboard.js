

        document.addEventListener("DOMContentLoaded", () => {
            // Product Redirect
            document.querySelectorAll('.product-card').forEach(card => {
                card.addEventListener('click', () => {
                    const id = card.dataset.id;
                    if (id) window.location.href = `/views/customer/product-details.php?id=${id}`;
                });
                card.addEventListener('mouseenter', () => card.style.transform = 'translateY(-8px)');
                card.addEventListener('mouseleave', () => card.style.transform = 'translateY(0)');
            });

            // Carousel Setup
            const imgs = [
                '/assets/images/product1.png',
                '/assets/images/product2.png',
            ];
            const data = [{
                    title: "Bobs red mill whole wheat",
                    price: "$429.12",
                    meta: "⏰ 270:13:10:32",
                    rating: "⭐ 4.5 (15 reviews)",
                    note: "100 sold in last 35 hour"
                },
                {
                    title: "Organic Coconut Oil",
                    price: "$199.50",
                    meta: "⏰ 120:05:04:12",
                    rating: "⭐ 4.7 (28 reviews)",
                    note: "56 sold recently"
                },

            ];

            const track = document.querySelector('#discount-carousel .dc-track');
            const thumbs = document.querySelector('#discount-carousel .dc-thumbs');
            const left = document.querySelector('#discount-carousel .dc-left');
            const right = document.querySelector('#discount-carousel .dc-right');
            const fileInput = document.getElementById('dcFileInput');
            let index = 0;

            const escapeHTML = s => s.replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));

            const renderSlides = () => {
                track.innerHTML = thumbs.innerHTML = '';
                imgs.forEach((img, i) => {
                    const d = data[i % data.length];
                    track.innerHTML += `
                <li class="dc-slide" data-idx="${i}">
                    <div class="dc-image-wrap">
                        <div class="dc-badge"><div>${Math.floor(Math.random()*60)+10}%<small>DISCOUNT</small></div></div>
                        <img src="${img}" alt="${escapeHTML(d.title)}">
                    </div>
                    <div class="dc-details">
                        <div class="dc-meta">${escapeHTML(d.meta)}</div>
                        <div class="dc-title">${escapeHTML(d.title)}</div>
                        <div class="dc-rating">${escapeHTML(d.rating)}</div>
                        <div class="dc-price">${escapeHTML(d.price)}</div>
                        <div class="dc-actions">
                            <button class="dc-btn-ghost">Add to bucket</button>
                            <button class="dc-btn-primary">Buy now</button>
                        </div>
                        <div class="dc-links">
                            <a href="#">Add to wishlist</a> | <a href="#">Compare</a>
                        </div>
                        <div class="dc-atrib">${escapeHTML(d.note)}</div>
                    </div>
                </li>`;
                    thumbs.innerHTML += `<div class="dc-thumb" data-idx="${i}"><img src="${img}" alt="${escapeHTML(d.title)}"></div>`;
                });
                update();
            };

            const update = () => {
                document.querySelectorAll('.dc-thumb').forEach(t => t.classList.remove('active'));
                const active = document.querySelector(`.dc-thumb[data-idx="${index}"]`);
                if (active) active.classList.add('active');
                const width = document.querySelector('#discount-carousel .dc-track-wrap').clientWidth;
                track.style.transform = `translateX(-${index * width}px)`;
            };

            const prev = () => (index = (index - 1 + imgs.length) % imgs.length, update());
            const next = () => (index = (index + 1) % imgs.length, update());
            const go = i => (index = i % imgs.length, update());

            left.addEventListener('click', prev);
            right.addEventListener('click', next);
            window.addEventListener('resize', update);
            thumbs.addEventListener('click', e => e.target.closest('.dc-thumb') && go(e.target.closest('.dc-thumb').dataset.idx));

            fileInput?.addEventListener('change', e => {
                const files = [...e.target.files].filter(f => f.type.startsWith('image/'));
                if (!files.length) return;
                files.forEach(f => {
                    imgs.push(URL.createObjectURL(f));
                    data.push({
                        title: f.name,
                        price: "—",
                        meta: "⏰ now",
                        rating: "⭐ —",
                        note: "Uploaded image"
                    });
                });
                renderSlides();
                go(imgs.length - files.length);
            });

            // Swipe support
            const container = document.querySelector('#discount-carousel .dc-track-wrap');
            let startX = 0;
            container.addEventListener('touchstart', e => startX = e.touches[0].clientX);
            container.addEventListener('touchend', e => {
                const diff = e.changedTouches[0].clientX - startX;
                if (diff < -30) next();
                if (diff > 30) prev();
            });

            // User Dropdown
            const userIcon = document.getElementById("userIcon");
            const userMenu = document.getElementById("userMenu");
            userIcon.addEventListener("click", e => {
                e.stopPropagation();
                userMenu.classList.toggle("show");
            });
            document.addEventListener("click", e => {
                if (!userMenu.contains(e.target) && !userIcon.contains(e.target)) userMenu.classList.remove("show");
            });

            renderSlides();
        });
