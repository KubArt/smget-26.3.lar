import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto'; // Добавляем авто-импорт всех компонентов

window.Alpine = Alpine;
window.Chart = Chart; // Делаем Chart глобальным для использования в Blade

Alpine.start();
