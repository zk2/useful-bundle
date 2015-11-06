(function () {
    if (window.define)var e = window.define;
    if (window.require)var t = window.require;
    if (window.jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd)var e = jQuery.fn.select2.amd.define, t = jQuery.fn.select2.amd.require;
    e("select2/i18n/uk", [], function () {
        function e(e, t, n, r) {
            return [11, 12, 13, 14].indexOf(e % 100) !== -1 ? r : e % 10 === 1 ? t : [2, 3, 4].indexOf(e % 10) !== -1 ? n : r
        }

        return {
            errorLoading: function () {
                return "Неможливо завантажити результати"
            }, inputTooLong: function (t) {
                var n = t.input.length - t.maximum;
                return "Будь ласка, видаліть " + n + " " + e(t.maximum, "літеру", "літери", "літер")
            }, inputTooShort: function (e) {
                var t = e.minimum - e.input.length;
                return "Будь ласка, введіть " + t + " або більше літер"
            }, loadingMore: function () {
                return "Завантаження інших результатів…"
            }, maximumSelected: function (t) {
                return "Ви можете вибрати лише " + t.maximum + " " + e(t.maximum, "пункт", "пункти", "пунктів")
            }, noResults: function () {
                return "Нічого не знайдено"
            }, searching: function () {
                return "Пошук…"
            }
        }
    }), t("jquery.select2"), jQuery.fn.select2.amd = {define: e, require: t}
})();