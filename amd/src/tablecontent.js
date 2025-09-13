/* eslint no-trailing-spaces: "off", no-unused-vars: "off" */
/* eslint-disable max-len */
/* eslint-disable no-unused-vars */
/* eslint-disable no-console */
define(['core/ajax', 'core/str', 'core/notification', 'jquery'], function(Ajax, Str, Notification, $) {
    return {
        /**
         * Initialize the table content module
         * @param {Object} params - Configuration parameters
         * @param {number} params.cmid - Course module ID
         * @param {number} params.templateid - Template ID
         */
        init: function(params) {
            console.log("📦 ZAŁADOWANO plik tablecontent.js");
            setTimeout(() => {
                console.log("🚀 init funkcja została uruchomiona");

                const el = document.querySelector('#valuemapdoc-content-table');
                if (!el) {
                    console.error("❌ Nie znaleziono elementu DOM: #valuemapdoc-content-table");
                    return;
                }

                const courseid = el.dataset.courseid;
                const cmid = params?.cmid || el.dataset.cmid;
                const templateid = params?.templateid || el.dataset.templateid;

                // Zapisanie parametrów w instancji dla późniejszego użycia
                this.courseid = courseid;
                this.cmid = cmid;
                this.templateid = templateid;
                this.tableElement = el;

                // Inicjalizacja przycisku odświeżania
                this.initRefreshButton();

                this.loadTableContent(courseid, cmid, templateid, el);
            }, 0);
        },

        /**
         * Ładuje zawartość tabeli z dokumentami
         * @param {string|number} courseid - Course ID
         * @param {string|number} cmid - Course module ID
         * @param {string|number} templateid - Template ID
         * @param {HTMLElement} el - DOM element for the table
         * @param {Function} [callback] - Optional callback function
         */
        loadTableContent: function(courseid, cmid, templateid, el, callback) {
            Ajax.call([{
                methodname: 'mod_valuemapdoc_get_content_entries',
                args: {
                    courseid: courseid,
                    cmid: cmid,
                    include_master: templateid,
                },
            }])[0].then(function(response) {
                console.log("📥 Otrzymane dane z AJAX:", response);
                
                const normalizedData = response.map(entry => ({
                    ...entry,
                    opportunityname: entry.name
                }));

                let html = '';
                
                if (response.length === 0) {
                    // Empty state
                    html = `
                        <div class="alert alert-info text-center">
                            <i class="fa fa-info-circle fa-2x mb-2" aria-hidden="true"></i>
                            <h5>Brak dokumentów</h5>
                            <p>Nie ma jeszcze utworzonych dokumentów. Wygeneruj pierwszy dokument, aby rozpocząć.</p>
                        </div>
                    `;
                } else {
                    // Generate table
                    html = `
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">Nazwa dokumentu</th>
                                        <th scope="col">Szablon</th>
                                        <th scope="col">Utworzony</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Ocena</th>
                                        <th scope="col" class="text-center">Akcje</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    response.forEach(entry => {
                        console.log("🔍 Przetwarzanie wpisu:", entry);
                        const templateName = entry.templatename || 'Brak szablonu';
                        const createdDate = entry.timecreated;
                        
                        // Status content
                        let statusBadge = '';
                        if (entry.status === 'pending') {
                            statusBadge = '<span class="badge badge-warning"><i class="fa fa-clock-o"></i> Generowanie</span>';
                        } else if (entry.status === 'error') {
                            statusBadge = '<span class="badge badge-danger"><i class="fa fa-exclamation-triangle"></i> Błąd</span>';
                        } else {
                            // Check visibility
                            if (entry.visibility == 1) {
                                statusBadge = '<span class="badge badge-warning"><i class="fa fa-lock"></i> Prywatny</span>';
                            } else {
                                statusBadge = '<span class="badge badge-success"><i class="fa fa-globe"></i> Współdzielony</span>';
                            }
                        }

                        // Rating display
                        let ratingDisplay = '<span class="text-muted">Nie oceniony</span>';
                        if (entry.effectiveness !== null && entry.effectiveness !== undefined) {
                            if (entry.effectiveness == 1) {
                                ratingDisplay = '<span class="text-success"><i class="fa fa-thumbs-up"></i> Dobry</span>';
                            } else if (entry.effectiveness == 0) {
                                ratingDisplay = '<span class="text-warning"><i class="fa fa-meh-o"></i> Neutralny</span>';
                            } else if (entry.effectiveness == -1) {
                                ratingDisplay = '<span class="text-danger"><i class="fa fa-thumbs-down"></i> Zły</span>';
                            }
                        }

                        // Action buttons
                        let actionButtons = '';
                        if (entry.status === 'pending') {
                            actionButtons = `
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-secondary btn-sm" disabled>
                                        <i class="fa fa-clock-o"></i>
                                    </button>
                                    <a href="${M.cfg.wwwroot}/mod/valuemapdoc/rate_content.php?id=${cmid}&docid=${entry.id}" 
                                       class="btn btn-outline-primary btn-sm" 
                                       title="Podgląd i ocena">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm delete-document" 
                                            data-docid="${entry.id}" 
                                            data-docname="${entry.name}"
                                            title="Usuń dokument">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            `;
                        } else if (entry.status === 'error') {
                            actionButtons = `
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-danger btn-sm" disabled>
                                        <i class="fa fa-exclamation-triangle"></i>
                                    </button>
                                    <a href="${M.cfg.wwwroot}/mod/valuemapdoc/rate_content.php?id=${cmid}&docid=${entry.id}" 
                                       class="btn btn-outline-primary btn-sm" 
                                       title="Podgląd i ocena">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm delete-document" 
                                            data-docid="${entry.id}" 
                                            data-docname="${entry.name}"
                                            title="Usuń dokument">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            `;
                        } else {
                            actionButtons = `
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="${M.cfg.wwwroot}/mod/valuemapdoc/rate_content.php?id=${cmid}&docid=${entry.id}" 
                                       class="btn btn-outline-primary btn-sm" 
                                       title="Podgląd i ocena">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="${M.cfg.wwwroot}/mod/valuemapdoc/edit_content.php?id=${cmid}&docid=${entry.id}" 
                                       class="btn btn-outline-secondary btn-sm" 
                                       title="Edytuj">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="${M.cfg.wwwroot}/mod/valuemapdoc/tune_content.php?id=${cmid}&docid=${entry.id}" 
                                       class="btn btn-outline-info btn-sm" 
                                       title="Dostraj">
                                        <i class="fa fa-cog"></i>
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm delete-document" 
                                            data-docid="${entry.id}" 
                                            data-docname="${entry.name}"
                                            title="Usuń dokument">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            `;
                        }

                        html += `
                            <tr class="document-row" data-docid="${entry.id}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fa fa-file-text-o text-primary mr-2" aria-hidden="true"></i>
                                        <div>
                                            <strong>${entry.name}</strong>
                                            ${entry.content ? `<br><small class="text-muted">${entry.content}</small>` : ''}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">${templateName}</span>
                                </td>
                                <td>
                                    <small>${createdDate}</small>
                                    ${entry.timemodified && entry.timemodified !== entry.timecreated ? 
                                        `<br><small class="text-muted">Zmieniony: ${new Date(entry.timemodified * 1000).toLocaleDateString('pl-PL')}</small>` : ''}
                                </td>
                                <td>
                                    ${statusBadge}
                                </td>
                                <td>
                                    ${ratingDisplay}
                                </td>
                                <td class="text-center">
                                    ${actionButtons}
                                </td>
                            </tr>
                        `;
                    });

                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                }

                // Remove initial documents and show table
                document.querySelector('.initial-documents')?.remove();
                el.classList.remove('d-none');
                el.innerHTML = html;

                // Add hover effects and interactions
                this.addTableInteractions(courseid, cmid, templateid, el);

                // Wywołaj callback jeśli został przekazany
                if (typeof callback === 'function') {
                    callback();
                }

            }.bind(this)).catch(function(error) {
                console.error("❌ AJAX error:", error);
                el.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                        Wystąpił błąd podczas ładowania dokumentów. 
                        <button class="btn btn-sm btn-outline-danger ml-2" onclick="location.reload()">
                            <i class="fa fa-refresh"></i> Odśwież
                        </button>
                    </div>
                `;
                
                // Wywołaj callback nawet w przypadku błędu
                if (typeof callback === 'function') {
                    callback();
                }
            });
        },

        /**
         * Adds interactive behavior to the documents table
         * @param {string|number} courseid - Course ID
         * @param {string|number} cmid - Course module ID  
         * @param {string|number} templateid - Template ID
         * @param {HTMLElement} el - DOM element for the table
         */
        addTableInteractions: function(courseid, cmid, templateid, el) {
            // Hover effect dla wierszy
            const rows = document.querySelectorAll('.document-row');
            rows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.classList.add('table-active');
                });
                row.addEventListener('mouseleave', function() {
                    this.classList.remove('table-active');
                });

                // Podwójne kliknięcie otwiera podgląd
                row.addEventListener('dblclick', function() {
                    const viewLink = this.querySelector('a[title*="Podgląd"]');
                    if (viewLink) {
                        window.open(viewLink.href, '_blank');
                    }
                });
            });

            // Obsługa przycisków usuwania
            const deleteButtons = document.querySelectorAll('.delete-document');
            deleteButtons.forEach(button => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    const docId = button.dataset.docid;
                    const docName = button.dataset.docname;
                    
                    this.showDeleteConfirmation(docId, docName, courseid, cmid, templateid, el);
                });
            });

            console.log("✅ Dodano interakcje do tabeli");
        },

        /**
         * Pokazuje dialog potwierdzenia usunięcia
         * @param {string|number} docId - Document ID to delete
         * @param {string} docName - Document name for confirmation
         * @param {string|number} courseid - Course ID
         * @param {string|number} cmid - Course module ID
         * @param {string|number} templateid - Template ID
         * @param {HTMLElement} el - DOM element for the table
         */
        showDeleteConfirmation: function(docId, docName, courseid, cmid, templateid, el) {
            // Tworzenie modalnego okna potwierdzenia
            const modalHtml = `
                <div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteConfirmModalLabel">
                                    <i class="fa fa-exclamation-triangle text-warning"></i> 
                                    Potwierdzenie usunięcia
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>Czy na pewno chcesz usunąć dokument:</p>
                                <p><strong>"${docName}"</strong></p>
                                <div class="alert alert-warning">
                                    <i class="fa fa-warning"></i>
                                    <strong>Uwaga:</strong> Ta operacja jest nieodwracalna!
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    <i class="fa fa-times"></i> Anuluj
                                </button>
                                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                                    <i class="fa fa-trash"></i> Usuń dokument
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Usuń istniejący modal jeśli istnieje
            const existingModal = document.getElementById('deleteConfirmModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Dodaj modal do body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Pokaż modal
            const modal = document.getElementById('deleteConfirmModal');
            $(modal).modal('show');
            
            // Obsługa przycisku potwierdzenia
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            confirmBtn.addEventListener('click', () => {
                this.deleteDocument(docId, courseid, cmid, templateid, el);
                $(modal).modal('hide');
            });
            
            // Usuń modal po zamknięciu
            $(modal).on('hidden.bs.modal', function() {
                modal.remove();
            });
        },

        /**
         * Usuwa dokument za pomocą AJAX
         * @param {string|number} docId - Document ID to delete
         * @param {string|number} courseid - Course ID
         * @param {string|number} cmid - Course module ID
         * @param {string|number} templateid - Template ID
         * @param {HTMLElement} el - DOM element for the table
         */
        deleteDocument: function(docId, courseid, cmid, templateid, el) {
            console.log("🗑️ Usuwanie dokumentu ID:", docId);
            
            // Znajdź wiersz z dokumentem i pokaż loader
            const row = document.querySelector(`tr[data-docid="${docId}"]`);
            if (row) {
                row.style.opacity = '0.5';
                row.innerHTML = `
                    <td colspan="6" class="text-center">
                        <i class="fa fa-spinner fa-spin"></i> Usuwanie dokumentu...
                    </td>
                `;
            }

            Ajax.call([{
                methodname: 'mod_valuemapdoc_delete_entry',
                args: {
                    entryid: parseInt(docId)
                },
            }])[0].then((response) => {
                console.log("✅ Dokument usunięty pomyślnie:", response);
                
                // Pokaż powiadomienie o sukcesie
                Notification.addNotification({
                    message: 'Dokument został pomyślnie usunięty.',
                    type: 'success'
                });
                
                // Przeładuj tabelę
                this.loadTableContent(courseid, cmid, templateid, el);
                
            }).catch((error) => {
                console.error("❌ Błąd podczas usuwania dokumentu:", error);
                
                // Pokaż powiadomienie o błędzie
                Notification.addNotification({
                    message: 'Wystąpił błąd podczas usuwania dokumentu. Spróbuj ponownie.',
                    type: 'error'
                });
                
                // Przeładuj tabelę aby przywrócić stan
                this.loadTableContent(courseid, cmid, templateid, el);
            });
        },

        /**
         * Inicjalizuje przycisk odświeżania
         */
        initRefreshButton: function() {
            const refreshBtn = document.querySelector('#refresh-documents-btn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', (event) => {
                    event.preventDefault();
                    this.refreshDocumentsList();
                });
                console.log("✅ Zainicjalizowano przycisk odświeżania");
            } else {
                console.warn("⚠️ Nie znaleziono przycisku odświeżania (#refresh-documents-btn)");
            }
        },

        /**
         * Odświeża listę dokumentów
         */
        refreshDocumentsList: function() {
            console.log("🔄 Odświeżanie listy dokumentów...");
            
            const refreshBtn = document.querySelector('#refresh-documents-btn');
            const refreshIcon = refreshBtn?.querySelector('i');
            const refreshText = refreshBtn?.querySelector('.btn-text');
            
            // Pokaż stan ładowania na przycisku
            if (refreshBtn) {
                refreshBtn.disabled = true;
                if (refreshIcon) {
                    refreshIcon.className = 'fa fa-spinner fa-spin';
                }
                if (refreshText) {
                    refreshText.textContent = 'Odświeżanie...';
                }
            }
            
            // Pokaż loader na tabeli
            if (this.tableElement) {
                this.tableElement.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fa fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                        <p class="text-muted">Ładowanie dokumentów...</p>
                    </div>
                `;
            }
            
            // Załaduj dane ponownie
            this.loadTableContent(this.courseid, this.cmid, this.templateid, this.tableElement, () => {
                // Callback po załadowaniu - przywróć stan przycisku
                if (refreshBtn) {
                    refreshBtn.disabled = false;
                    if (refreshIcon) {
                        refreshIcon.className = 'fa fa-refresh';
                    }
                    if (refreshText) {
                        refreshText.textContent = 'Odśwież';
                    }
                }
                /*
                // Pokaż powiadomienie o odświeżeniu
                Notification.addNotification({
                    message: 'Lista dokumentów została odświeżona.',
                    type: 'success'
                });
                */
                console.log("✅ Lista dokumentów odświeżona pomyślnie");
            });
        }
    };
});