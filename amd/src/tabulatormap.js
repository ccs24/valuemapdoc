/* eslint no-trailing-spaces: "off", no-unused-vars: "off" */
/* eslint-disable max-len */
/* eslint-disable no-unused-vars */
/* eslint-disable no-console */
/* eslint-disable jsdoc/require-param */
define('mod_valuemapdoc/tabulatormap', [
    'mod_valuemapdoc/tabulatorlib', 
    'core/ajax', 
    'core_user/repository',
    'core/notification',
    'jquery'
], function(
    Tabulator, 
    Ajax, 
    UserRepository,
    Notification,
    $
) {

    

    return {
        init: function() {
            console.log('[tabulatormap] Module loaded');

            const el = document.querySelector('#valuemap-table-js');
            if (!el) {
                console.warn('[tabulatormap] Table element not found');
                return;
            }

            const columns = JSON.parse(el.dataset.columns);
            const courseid = el.dataset.courseid;
            const cmid = el.dataset.cmid;
            const filtercmid = el.dataset.filtercmid || '';
            const groupid = document.querySelector('#groupfilter')?.value || 0;

            const fullscreenBtn = document.querySelector('#toggle-fullscreen');
            if (fullscreenBtn) {
                fullscreenBtn.addEventListener('click', () => {
                    document.body.classList.toggle('valuemapdoc-fullscreen');
                    if (document.body.classList.contains('valuemapdoc-fullscreen')) {
                        fullscreenBtn.textContent = '❎ Zamknij pełny ekran';
                    } else {
                        fullscreenBtn.textContent = '🔳 Pełny ekran';
                    }
                });        
            }

            /**
             * Generate Tabulator columns with editable fields and username column
             * @param {Array} columns Array of column definitions received from dataset
             * @returns {Array} Enhanced column definitions with selection and username
             */
            function prepareColumns(columns) {
                const enhancedColumns = [
                    {
                        formatter: "rowSelection",
                        titleFormatter: "rowSelection",
                        hozAlign: "center",
                        headerSort: false,
                        width: 50
                    },
                    ...columns.map(col => ({
                        ...col,
                        headerFilter: "input",
                        editable: (cell) => cell.getRow().getData().ismaster !== 1,
                        editor: "textarea"
                    })),
                    // Dodaj kolumnę username na końcu
                    {
                        title: "Autor",
                        field: "username", 
                        hozAlign: "left",
                        headerSort: true,
                        width: 120,
                        headerFilter: "input",
                        editable: false,
                        formatter: function(cell, formatterParams) {
                            const value = cell.getValue();
                            const data = cell.getRow().getData();
                            
                            if (data.ismaster === 1) {
                                return '<i class="fa fa-star text-warning" title="Master entry"></i> ' + value;
                            }
                            
                            return value;
                        }
                    }
                ];
                
                return enhancedColumns;
            }

            /**
             * Pobiera username aktualnego użytkownika z odpowiedzi AJAX
             * @param {Array} response - Dane z serwera
             * @returns {string} Username aktualnego użytkownika
             */
            function getUsernameFromResponse(response) {
                const currentUserId = M.cfg.userid;
                
                const userEntry = response.find(entry => entry.userid == currentUserId);
                
                if (userEntry && userEntry.username) {
                    return userEntry.username;
                }
                
                return M.cfg.fullname || M.cfg.username || 'Ja';
            }

            /**
             * Dodaje przycisk przełączania filtra użytkownika
             * @param {Object} table - Instancja Tabulatora
             * @param {string} currentUsername - Username aktualnego użytkownika
             */
            function addUserFilterToggle(table, currentUsername) {
                const toolbar = document.querySelector('.btn-toolbar');
                if (!toolbar) return;
                
                const filterContainer = document.createElement('div');
                filterContainer.className = 'btn-group ms-auto';
                filterContainer.setAttribute('role', 'group');
                
                const toggleButton = document.createElement('button');
                toggleButton.className = 'btn btn-outline-info btn-sm';
                toggleButton.setAttribute('type', 'button');
                toggleButton.innerHTML = '<i class="fa fa-user"></i> Tylko moje';
                toggleButton.title = 'Przełącz między moimi wpisami a wszystkimi';
                
                let showingOnlyMine = true;
                
                toggleButton.addEventListener('click', function() {
                    if (showingOnlyMine) {
                        table.clearHeaderFilter("username");
                        toggleButton.innerHTML = '<i class="fa fa-users"></i> Wszystkie';
                        toggleButton.className = 'btn btn-outline-secondary btn-sm';
                        showingOnlyMine = false;
                    } else {
                        table.setHeaderFilterValue("username", currentUsername);
                        toggleButton.innerHTML = '<i class="fa fa-user"></i> Tylko moje';
                        toggleButton.className = 'btn btn-outline-info btn-sm';
                        showingOnlyMine = true;
                    }
                });
                
                filterContainer.appendChild(toggleButton);
                toolbar.appendChild(filterContainer);
            }

            const enhancedColumns = prepareColumns(columns);

            Ajax.call([{
                methodname: 'mod_valuemapdoc_get_entries',
                args: {
                    courseid: courseid,
                    cmid: cmid,
                    include_master: filtercmid,
                    groupid: groupid
                },
            }])[0].done(function(response) {
                console.log('[tabulatormap] Entries loaded:', response.length, 'records');

                // Pobierz username aktualnego użytkownika
                const currentUsername = getUsernameFromResponse(response);

                const table = new Tabulator(el, {
                    data: response,
                    columns: enhancedColumns,
                    layout:"fitDataTable",
                    height: "100%",
                    width: "100%",
                    pagination: true,
                    paginationSize: 20,
                    placeholder: "Brak danych do wyświetlenia",
                    rowFormatter: function(row) {
                        const data = row.getData();
                        if (data.ismaster === 1) {
                            row.getElement().style.backgroundColor = '#eaffea';
                            row.getElement().classList.add('ismaster');
                        }
                    },
                    selectable: true,
                });

                // Ustaw domyślny filtr na kolumnie username (tylko wpisy użytkownika)
                table.setHeaderFilterValue("username", currentUsername);
                
                // Dodaj przycisk przełączania filtra użytkownika
                addUserFilterToggle(table, currentUsername);

                // Obsługa edycji komórek
                table.on("cellEdited", function(cell){
                    const updatedData = cell.getRow().getData();

                    Ajax.call([{
                        methodname: 'mod_valuemapdoc_update_cell',
                        args: {
                            id: updatedData.id,
                            field: cell.getField(),
                            value: cell.getValue()
                        }
                    }])[0].done(function(response) {
                        console.log('[tabulatormap] Cell updated successfully');
                    }).fail(function(error) {
                        console.error('[tabulatormap] Error updating cell:', error);
                        alert("Nie udało się zapisać zmian.");
                    });
                });

                // Obsługa podwójnego kliknięcia tylko dla rekordów master
                table.on("rowDblClick", function(e, row) {
                    const data = row.getData();
                    if (data.ismaster === 1) {
                        const rateUrl = `${M.cfg.wwwroot}/mod/valuemapdoc/edit.php?id=${cmid}&entryid=${data.id}`;
                        window.open(rateUrl);
                    }
                });

                // Search functionality
                const searchInput = document.querySelector('#valuemap-search');
                if (searchInput) {
                    searchInput.addEventListener('input', function () {
                        const filterValue = this.value.toLowerCase();
                        table.setFilter((data) => {
                            return columns.some(col => {
                                const field = col.field;
                                return data[field]?.toString().toLowerCase().includes(filterValue);
                            });
                        });
                    });
                }

                // UPROSZCZONE GENERATE BUTTON - bez hierarchy
                const generateButton = document.querySelector('#generate-button');
                if (generateButton) {
                    generateButton.addEventListener('click', function () {
                        const selectedData = table.getSelectedData();
                        if (!selectedData.length) {
                            alert("Proszę zaznaczyć co najmniej jeden rekord.");
                            return;
                        }

                        const templateSelect = document.querySelector('#templateid');
                        if (!templateSelect || !templateSelect.value) {
                            alert("Wybierz szablon przed generowaniem dokumentu.");
                            return;
                        }

                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = generateButton.dataset.action || 'generate.php';

                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id';
                        idInput.value = cmid;
                        form.appendChild(idInput);

                        const templateInput = document.createElement('input');
                        templateInput.type = 'hidden';
                        templateInput.name = 'templateid';
                        templateInput.value = templateSelect.value;
                        form.appendChild(templateInput);

                        selectedData.forEach(entry => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'entryids[]';
                            input.value = entry.id;
                            form.appendChild(input);
                        });

                        // Add filenameprefix if present
                        const filenameprefix = document.getElementById('filenameprefix');
                        if (filenameprefix && filenameprefix.value) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'filenameprefix';
                            input.value = filenameprefix.value;
                            form.appendChild(input);
                        }

                        document.body.appendChild(form);
                        form.submit();
                    });
                }

                // Bulk operations handlers
                const editBulkButton = document.querySelector('#edit-bulk-button');
                if (editBulkButton) {
                    editBulkButton.addEventListener('click', function () {
                        const selectedData = table.getSelectedData();
                        if (!selectedData.length) {
                            alert("Zaznacz rekordy do edycji.");
                            return;
                        }

                        if (selectedData.length === 1) {
                            const id = selectedData[0].id;
                            const url = `${M.cfg.wwwroot}/mod/valuemapdoc/edit.php?id=${cmid}&entryid=${id}`;
                            window.location.href = url;
                        } else {
                            const ids = selectedData.map(r => r.id);
                            const url = new URL(`${M.cfg.wwwroot}/mod/valuemapdoc/edit_bulk.php`);
                            url.searchParams.append('id', cmid);
                            url.searchParams.append('entryids', ids.join(','));
                            window.open(url.toString());
                        }
                    });
                }

                // Duplicate functionality
                const duplicateBulkButton = document.querySelector('#duplicate-bulk-button');
                if (duplicateBulkButton) {
                    duplicateBulkButton.addEventListener('click', function () {
                        const selectedData = table.getSelectedData();
                        if (!selectedData.length) {
                            alert("Zaznacz rekordy do powielenia.");
                            return;
                        }
                        const ids = selectedData.map(r => r.id);
                        Ajax.call([{
                            methodname: 'mod_valuemapdoc_create_entries_bulk',
                            args: {
                                entryids: ids,
                                cmid: cmid
                            }
                        }])[0].done((response) => {
                            table.updateOrAddData(response);
                            table.deselectRow();
                        }).fail((error) => {
                            console.error("[tabulatormap] Error duplicating records:", error);
                            alert("Nie udało się powielić rekordy.");
                        });
                    });
                }

                // Add empty line
                const emptyBulkButton = document.querySelector('#empty-bulk-button');
                if (emptyBulkButton) {
                    emptyBulkButton.addEventListener('click', function () {
                        Ajax.call([{
                            methodname: 'mod_valuemapdoc_create_entries_bulk',
                            args: {
                                entryids: {},
                                cmid: cmid
                            }
                        }])[0].done(function(response) {
                            table.updateOrAddData(response);
                            table.deselectRow();
                        }).fail((error) => {
                            console.error("[tabulatormap] Error creating empty records:", error);
                        });
                    });
                }

                // Row selection handler for enabling/disabling buttons
                table.on('rowSelectionChanged', function(data) {
                    const buttons = [
                        '#delete-bulk-button', 
                        '#generate-button', 
                        '#move-bulk-button', 
                        '#edit-bulk-button', 
                        '#duplicate-bulk-button'
                    ];

                    buttons.forEach(selector => {
                        const button = document.querySelector(selector);
                        if (button) {
                            if (data.length > 0) {
                                button.disabled = false;
                                button.removeAttribute('disabled');
                                button.classList.remove('disabled');
                            } else {
                                button.setAttribute('disabled', true);
                                button.classList.add('disabled');
                                button.disabled = true;
                            }
                        }
                    });
                });

                // Delete bulk handler
                const deleteBulkButton = document.querySelector('#delete-bulk-button');
                if (deleteBulkButton) {
                    deleteBulkButton.addEventListener('click', function () {
                        const selectedData = table.getSelectedData();
                        if (!selectedData.length) {
                            alert("Zaznacz rekordy do usunięcia.");
                            return;
                        }
                        const filteredData = selectedData.filter(r => r.ismaster !== 1);
                        const ids = filteredData.map(r => r.id);
                        const blocked = selectedData.length - filteredData.length;
                        if (blocked > 0) {
                            alert(`${blocked} rekord(ów) typu Master nie mogą zostać usunięte i zostały pominięte.`);
                        }
                        if (!ids.length) {
                            alert("Nie zaznaczono żadnych rekordów możliwych do usunięcia.");
                            return;
                        }
                        if (confirm(`Czy na pewno chcesz usunąć ${ids.length} rekordów?`)) {
                            Ajax.call([{
                                methodname: 'mod_valuemapdoc_delete_bulk',
                                args: {
                                    entryids: ids,
                                    cmid: cmid
                                }
                            }])[0].done(() => {
                                ids.forEach(id => table.deleteRow(id));
                                table.deselectRow();
                            }).fail((error) => {
                                console.error("[tabulatormap] Error deleting records:", error);
                                alert("Nie udało się usunąć rekordów.");
                            });
                        }
                    });
                }

                // Move bulk handler
                const moveBulkButton = document.querySelector('#move-bulk-button');
                if (moveBulkButton) {
                    moveBulkButton.addEventListener('click', function () {
                        const selectedData = table.getSelectedData();
                        if (!selectedData.length) {
                            alert("Zaznacz rekordy do przeniesienia.");
                            return;
                        }
                        Ajax.call([{
                            methodname: 'mod_valuemapdoc_get_master_instances',
                            args: { courseid: courseid }
                        }])[0].done(function(instances) {
                            if (instances.length === 0) {
                                alert("Brak dostępnych aktywności typu Master.");
                                return;
                            }

                            // Prosta implementacja - wybierz pierwszą dostępną instancję
                            if (instances.length === 1) {
                                const targetcmid = instances[0].cmid;
                                const ids = selectedData.map(r => r.id);
                                Ajax.call([{
                                    methodname: 'mod_valuemapdoc_move_bulk',
                                    args: {
                                        entryids: ids,
                                        targetcmid: targetcmid
                                    }
                                }])[0].done(() => {
                                    ids.forEach(id => table.deleteRow(id));
                                }).fail((error) => {
                                    console.error("[tabulatormap] Error moving records:", error);
                                    alert("Nie udało się przenieść rekordów.");
                                });
                            } else {
                                alert("Znaleziono wiele instancji Master. Wybierz konkretną instancję z listy.");
                            }
                        }).fail((error) => {
                            console.error("[tabulatormap] Error getting master instances:", error);   
                            alert("Nie udało się pobrać listy Master Map.");
                        });
                    });
                }

                // Group filter handler z zachowaniem filtra użytkownika
                const groupFilter = document.querySelector('#groupfilter');
                if (groupFilter) {
                    groupFilter.addEventListener('change', () => {
                        const selectedGroupId = groupFilter.value;
                        const currentFilterCmid = masterFilter?.value || filtercmid;
                        const currentUsernameFilter = table.getHeaderFilterValue("username");

                        Ajax.call([{
                            methodname: 'mod_valuemapdoc_get_entries',
                            args: {
                                courseid: courseid,
                                cmid: cmid,
                                include_master: currentFilterCmid,
                                groupid: parseInt(selectedGroupId)
                            },
                        }])[0].done(function(newResponse) {
                            table.setData(newResponse);
                            
                            // Przywróć filtr użytkownika po zmianie danych
                            if (currentUsernameFilter) {
                                table.setHeaderFilterValue("username", currentUsernameFilter);
                            }
                            
                        }).fail(function(error) {
                            console.error("[tabulatormap] Error loading data after group change:", error);
                        });
                    });
                }

                // Master filter handler z zachowaniem filtra użytkownika
                const masterFilter = document.querySelector('#masterfilter');
                if (masterFilter) {
                    masterFilter.addEventListener('change', () => {
                        const newFilterCmid = masterFilter.value;
                        const currentUsernameFilter = table.getHeaderFilterValue("username");

                        // Save user preference
                        UserRepository.setUserPreference('mod_valuemapdoc_masterfilters', cmid + ':' + newFilterCmid)
                            .then(() => {
                                console.log("[tabulatormap] Preference saved:", newFilterCmid);
                            })
                            .catch(error => {
                                console.error("[tabulatormap] Error saving preference:", error);
                            });

                        Ajax.call([{
                            methodname: 'mod_valuemapdoc_get_entries',
                            args: {
                                courseid: courseid,
                                cmid: cmid,
                                include_master: newFilterCmid,
                                groupid: document.querySelector('#groupfilter')?.value || 0
                            },
                        }])[0].done(function(newResponse) {
                            table.setData(newResponse);
                            
                            // Przywróć filtr użytkownika po zmianie danych
                            if (currentUsernameFilter) {
                                table.setHeaderFilterValue("username", currentUsernameFilter);
                            }
                            
                        }).fail(function(error) {
                            console.error("[tabulatormap] Error loading data after master change:", error);
                        });
                    });
                }

                // Window resize handler
                window.addEventListener('resize', () => {
                    table.redraw(true);
                });

            }).fail(function(error) {
                console.error("[tabulatormap] Error loading entries:", error);
                alert("Nie udało się załadować danych tabeli.");
            });
        }
    };
});