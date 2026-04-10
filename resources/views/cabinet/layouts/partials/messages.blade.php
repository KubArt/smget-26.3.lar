<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Функция для вызова уведомления OneUI
        function showNotification(message, type = 'info', icon = 'fa fa-info-circle') {
            One.helpers('jq-notify', {
                type: type,      // 'info', 'success', 'warning', 'danger'
                icon: icon,
                align: 'right',
                from: 'top',
                placeholder: '',
                message: message
            });
        }

        {{-- Проверка на Success --}}
        @if(session('success'))
        showNotification("{{ session('success') }}", 'success', 'fa fa-check-circle');
        @endif

        {{-- Проверка на Error (включая ошибки валидации) --}}
        @if(session('error'))
        showNotification("{{ session('error') }}", 'danger', 'fa fa-times-circle');
        @endif

        {{-- Проверка на Warning --}}
        @if(session('warning'))
        showNotification("{{ session('warning') }}", 'warning', 'fa fa-exclamation-triangle');
        @endif

        {{-- Если есть ошибки валидации $errors --}}
        @if ($errors->any())
        showNotification("Пожалуйста, исправьте ошибки в форме", 'danger', 'fa fa-exclamation-circle');
        @endif
    });
</script>
