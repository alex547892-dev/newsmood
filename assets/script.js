document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.mood-btn');
    const grid = document.querySelector('.news-grid');
    if (!grid) return;

    buttons.forEach(btn => {
        btn.addEventListener('click', async () => {
            const mood = btn.dataset.mood;
            // Переключаем активный класс
            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Показываем загрузку
            grid.style.opacity = '0.5';

            try {
                const response = await fetch(`/index.php?mood=${encodeURIComponent(mood)}&ajax=1`);
                if (!response.ok) {
                    throw new Error(`Server error: ${response.status}`);
                }
                const newsList = await response.json();

                // Обновляем карточки
                const cards = grid.querySelectorAll('.news-card');
                cards.forEach((card, index) => {
                    if (newsList[index]) {
                        const item = newsList[index];
                        const titleEl = card.querySelector('.card-title');
                        const excerptEl = card.querySelector('.card-excerpt');
                        const linkEl = card.querySelector('.card-link');
                        if (titleEl) titleEl.textContent = item.title;
                        if (excerptEl) excerptEl.textContent = item.excerpt;
                        if (linkEl) linkEl.href = `/news.php?id=${item.id}&mood=${mood}`;
                    }
                });
            } catch (err) {
                console.error('Ошибка загрузки настроения:', err);
                alert('Не удалось загрузить новости в выбранном настроении');
            } finally {
                grid.style.opacity = '1';
            }
        });
    });
});