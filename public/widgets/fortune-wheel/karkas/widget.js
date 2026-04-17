window.SmWidget_fortune_wheel = class extends SmWidget {
    constructor(settings, id, assets) {
        super(settings, id, assets);
        this.activeClass = 'sp-active'; // Используем тот же класс, что в мультикнопке
        this.currentRotation = 0;
        this.isSpinning = false;
    }

    mount() {
        // 1. Инжектируем стили
        this.injectStyles();

        // 2. Подготовка HTML
        const design = this.settings.design || {};
        let html = this.assets.html
            .replace(/{id}/g, this.id)
            .replace(/{title}/g, design.title || 'Удача!')
            .replace(/{description}/g, design.description || 'Крутите колесо!')
            .replace(/{btn_spin_text}/g, 'Играть')
            .replace(/{position}/g, this.settings.position || 'right');

        // 3. Создание контейнера через ядро
        this.createContainer(html, 'sfw-wheel-container');

        if (this.container) {
            this.drawWheel();
            this.bindEvents(); // Используем проверенный метод привязки событий
        }
    }

    bindEvents() {
        // Копируем проверенную логику кликов из работающего плагина
        const toggleBtn = this.container.querySelector('[data-sp-toggle]');
        const closeBtn = this.container.querySelector('[data-sp-close]');
        const spinBtn = this.container.querySelector('.sfw-spin-trigger');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', (e) => {
                this.container.classList.toggle(this.activeClass);
                this.track(this.container.classList.contains(this.activeClass) ? 'open' : 'close');
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.container.classList.remove(this.activeClass);
            });
        }

        if (spinBtn) {
            spinBtn.addEventListener('click', () => this.startSpin());
        }
    }

    drawWheel() {
        const canvas = document.getElementById(`sfw-canvas-${this.id}`);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const segments = this.settings.wheel.segments || [{label: 'Приз', bg_color: '#ccc'}];

        canvas.width = 300; canvas.height = 300;
        const arc = (2 * Math.PI) / segments.length;

        segments.forEach((s, i) => {
            const angle = i * arc;
            ctx.beginPath();
            ctx.fillStyle = s.bg_color || (i % 2 ? '#eee' : '#ddd');
            ctx.moveTo(150, 150);
            ctx.arc(150, 150, 140, angle, angle + arc);
            ctx.fill();

            // Текст сегмента
            ctx.save();
            ctx.translate(150, 150);
            ctx.rotate(angle + arc / 2);
            ctx.fillStyle = "#333";
            ctx.fillText(s.label, 70, 5);
            ctx.restore();
        });
    }

    startSpin() {
        if (this.isSpinning) return;
        this.isSpinning = true;

        const canvas = document.getElementById(`sfw-canvas-${this.id}`);
        const randomDeg = Math.floor(2000 + Math.random() * 2000);
        this.currentRotation += randomDeg;

        canvas.style.transition = "transform 4s cubic-bezier(0.15, 0, 0.15, 1)";
        canvas.style.transform = `rotate(${this.currentRotation}deg)`;

        setTimeout(() => {
            this.isSpinning = false;
            this.track('win');
            alert('Поздравляем!');
        }, 4000);
    }
};
