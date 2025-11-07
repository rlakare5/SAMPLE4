<div class="language-switcher">
    <select onchange="window.location.href='index.php?lang=' + this.value">
        <option value="en" <?= getCurrentLanguage() === 'en' ? 'selected' : '' ?>>English</option>
        <option value="hi" <?= getCurrentLanguage() === 'hi' ? 'selected' : '' ?>>हिंदी</option>
        <option value="mr" <?= getCurrentLanguage() === 'mr' ? 'selected' : '' ?>>मराठी</option>
    </select>
</div>

<style>
.language-switcher {
    display: inline-block;
}

.language-switcher select {
    padding: 0.5rem 1rem;
    border: 1px solid rgba(255,255,255,0.3);
    background: rgba(255,255,255,0.2);
    color: white;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
}

.language-switcher select option {
    color: #333;
}
</style>
