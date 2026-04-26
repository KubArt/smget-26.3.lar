<script>
    const data = @json($data);
    if (data.error) {
        alert(data.error);
        window.close();
    } else if (data.redirect) {
        // Если нужно выбрать счетчик, редиректим ГЛАВНОЕ окно, а не это
        window.opener.location.href = data.redirect;
        window.close();
    } else {
        window.opener.location.reload();
        window.close();
    }
</script>
