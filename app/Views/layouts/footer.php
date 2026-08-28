    </main>

    <div
    id="confirmation-modal"
    class="confirmation-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="confirmation-title"
    hidden
    >
    <article class="stat-card">
        <div class="confirmation-dialog">

            <h2 id="confirmation-title">
                Confirmar operação
            </h2>

            <p id="confirmation-message">
                Deseja realmente continuar com esta operação?
            </p>

            <div class="confirmation-actions">
                <button
                    type="button"
                    id="confirmation-cancel"
                    class="button button-outline"
                >
                    Cancelar
                </button>

                <button
                    type="button"
                    id="confirmation-confirm"
                    class="button button-danger"
                >
                    Sim, continuar
                </button>
        </div>
    </article>
    </div>

    <footer class="footer">
        &copy; <?= date('Y') ?> <?= e(APP_NAME) ?> — Sistema de Gestão de Lavandaria
    </footer>

    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
    
    </body>

    </html>