/* eslint-env es6 */
/* eslint no-trailing-spaces: "off", 
          no-unused-vars: "off" */
/* eslint-disable max-len */
/* eslint-disable no-unused-vars */
/* eslint-disable no-console */
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
        init: function(courseid, cmid, filtercmid, columns) {

            console.log('[tabulatormap] Module loaded');
            console.log('[tabulatormap] Course ID:', courseid, 'CM ID:', cmid, 'Filter CM ID:', filtercmid);
            console.log('[DEBUG] Columns received:', columns);

            // Parse columns if string
            if (typeof columns === 'string') {
                try {
                    columns = JSON.parse(columns);
                    console.log('[DEBUG] Parsed columns:', columns);
                } catch (e) {
                    console.error('[tabulatormap] Error parsing columns:', e);
                    return;
                }
            }

            if (!Array.isArray(columns)) {
                console.error('[tabulatormap] Columns is not an array');
                return;
            }

            var el = document.querySelector('#valuemap-table-js');
            if (!el) {
                console.warn('[tabulatormap] Table element not found');
                return;
            }

            var groupfilterEl = document.querySelector('#groupfilter');
            var groupid = groupfilterEl ? parseInt(groupfilterEl.value) || 0 : 0;
            var fullscreenBtn = document.querySelector('#toggle-fullscreen');
            var table; // Declare table variable

            if (fullscreenBtn) {
                fullscreenBtn.addEventListener('click', function() {
                    document.body.classList.toggle('valuemapdoc-fullscreen');
                    if (document.body.classList.contains('valuemapdoc-fullscreen')) {
                        fullscreenBtn.textContent = '❎ Zamknij pełny ekran';
                    } else {
                        fullscreenBtn.textContent = '🔳 Pełny ekran';
                    }
                });        
            }

            /**
             * Get username from response data for current user
             * @param {Array} response Response data from server
             * @returns {string} Current user's username
             */
            function getUsernameFromResponse(response) {
                var currentUserId = M.cfg.userid;
                var userEntry = response.find(function(entry) {
                    return entry.userid == currentUserId;
                });
                
                if (userEntry && userEntry.username) {
                    return userEntry.username;
                }
                
                return M.cfg.fullname || M.cfg.username || 'Ja';
            }

            /**
             * Generate Tabulator columns with editable fields and username column
             * @param {Array} columns Array of column definitions received from dataset
             * @returns {Array} Enhanced column definitions with selection and username
             */
            function prepareColumns(columns) {
                var enhancedColumns = [
                    {
                        formatter: "rowSelection",
                        titleFormatter: "rowSelection",
                        hozAlign: "center",
                        headerSort: false,
                        width: 50
                    }
                ];
                
                // Add mapped columns
                columns.forEach(function(col) {
                    enhancedColumns.push({
                        title: col.title,
                        field: col.field,
                        hozAlign: col.hozAlign,
                        headerSort: col.headerSort,
                        width: col.width,
                        headerFilter: "input",
                        editable: function(cell) {
                            return cell.getRow().getData().ismaster !== 1;
                        },
                        editor: "textarea"
                    });
                });
                
                // Add username column
                enhancedColumns.push({
                    title: "Autor",
                    field: "username", 
                    hozAlign: "left",
                    headerSort: true,
                    width: 120,
                    headerFilter: "input",
                    editable: false,
                    formatter: function(cell, formatterParams) {
                        var value = cell.getValue();
                        if (cell.getRow().getData().ismaster === 1) {
                            return '<i class="fa fa-star text-warning" title="Master entry"></i> ' + value;
                        }
                        return value;
                    }
                });

                return enhancedColumns;
            }

            var enhancedColumns = prepareColumns(columns);

            Ajax.call([{
                methodname: 'mod_valuemapdoc_get_entries',
                args: {
                    courseid: parseInt(courseid),
                    cmid: parseInt(cmid),
                    include_master: filtercmid ? parseInt(filtercmid) : 0,
                    groupid: groupid
                },
            }])[0].done(function(response) {
                console.log('[tabulatormap] Entries loaded:', response.length, 'records');

                var currentUsername = getUsernameFromResponse(response);

                table = new Tabulator(el, {
                    data: response,
                    columns: enhancedColumns,
                    layout: "fitDataTable",
                    height: "100%",
                    width: "100%",
                    pagination: true,
                    paginationSize: 20,
                    placeholder: "Brak danych do wyświetlenia",
                    rowFormatter: function(row) {
                        var data = row.getData();
                        if (data.ismaster === 1) {
                            row.getElement().style.backgroundColor = '#eaffea';
                            row.getElement().classList.add('ismaster');
                        }
                    },
                    selectable: true,
                });

                /**
                 * Function to reload table data from server
                 */
                function reloadTableData() {
                    Ajax.call([{
                        methodname: 'mod_valuemapdoc_get_entries',
                        args: {
                            courseid: parseInt(courseid),
                            cmid: parseInt(cmid),
                            include_master: filtercmid ? parseInt(filtercmid) : 0,
                            groupid: groupid
                        },
                    }])[0].done(function(response) {
                        console.log('[tabulatormap] Table data reloaded');
                        table.setData(response);
                    }).fail(function(error) {
                        console.error('[tabulatormap] Error reloading data:', error);
                    });
                }

                // Wait for table to build before setup
                table.on("tableBuilt", function(){
                    console.log('[tabulatormap] Table built successfully');
                    
                    // Set default filter to show only current user's entries
                    table.setHeaderFilterValue("username", currentUsername);
                    
                    // Add user filter toggle
                    addUserFilterToggle(table, currentUsername);
                });

                // Selection change handler - show/hide bulk actions
                table.on("rowSelectionChanged", function(data, rows){
                    var bulkActions = document.querySelector('#bulk-actions');
                    if (bulkActions) {
                        if (data.length > 0) {
                            bulkActions.style.display = 'block';
                        } else {
                            bulkActions.style.display = 'none';
                        }
                    }
                });

                // Handle cell editing
                table.on("cellEdited", function(cell){
                    var updatedData = cell.getRow().getData();

                    Ajax.call([{
                        methodname: 'mod_valuemapdoc_update_cell',
                        args: {
                            id: updatedData.id,
                            field: cell.getField(),
                            value: cell.getValue()
                        }
                    }])[0].done(function(response) {
                        console.log('[tabulatormap] Cell updated successfully');
                        // Optional: reload table data to ensure sync with database
                        // reloadTableData();
                    }).fail(function(error) {
                        console.error('[tabulatormap] Error updating cell:', error);
                        alert('Nie udało się zapisać zmian.');
                        // Revert cell value on error
                        cell.restoreOldValue();
                    });
                });

                // Handle double-click to open edit form
                table.on("rowDblClick", function(e, row){
                    var data = row.getData();
                    if (data.ismaster === 1) {
                        var rateUrl = M.cfg.wwwroot + '/mod/valuemapdoc/edit.php?id=' + cmid + '&entryid=' + data.id;
                        window.open(rateUrl);
                    }
                });

                /**
                 * Add user filter toggle button
                 * @param {Object} table Tabulator instance
                 * @param {string} currentUsername Current user's username
                 */
                function addUserFilterToggle(table, currentUsername) {
                    var toolbar = document.querySelector('.btn-toolbar');
                    if (!toolbar) { 
                        return;
                    }

                    var filterContainer = document.createElement('div');
                    filterContainer.className = 'btn-group ms-auto';
                    filterContainer.setAttribute('role', 'group');

                    var toggleButton = document.createElement('button');
                    toggleButton.className = 'btn btn-outline-info btn-sm';
                    toggleButton.setAttribute('type', 'button');
                    toggleButton.innerHTML = '<i class="fa fa-user"></i> Tylko moje';
                    toggleButton.title = 'Przełącz między moimi wpisami a wszystkimi';

                    var showingOnlyMine = true;

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

                // Global search functionality
                var searchInput = document.querySelector('#valuemap-search');
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        var filterValue = this.value.toLowerCase();
                        table.setFilter(function(data) {
                            return columns.some(function(col) {
                                var field = col.field;
                                var fieldValue = data[field];
                                return fieldValue && fieldValue.toString().toLowerCase().includes(filterValue);
                            });
                        });
                    });
                }

                // Edit button functionality
                var editBulkButton = document.querySelector('#edit-bulk-button');
                if (editBulkButton) {
                    editBulkButton.addEventListener('click', function() {
                        var selectedData = table.getSelectedData();
                        if (!selectedData.length) {
                            alert('Proszę zaznaczyć co najmniej jeden rekord.');
                            return;
                        }

                        if (selectedData.length === 1) {
                            // Single entry - open regular edit form
                            var id = selectedData[0].id;
                            var url = M.cfg.wwwroot + '/mod/valuemapdoc/edit.php?id=' + cmid + '&entryid=' + id;
                            window.location.href = url;
                        } else {
                            // Multiple entries - open bulk edit form
                            var ids = selectedData.map(function(r) {
                                return r.id;
                            });
                            var bulkUrl = new URL(M.cfg.wwwroot + '/mod/valuemapdoc/edit_bulk.php');
                            bulkUrl.searchParams.append('id', cmid);
                            bulkUrl.searchParams.append('entryids', ids.join(','));
                            window.open(bulkUrl.toString());
                        }
                    });
                }

                // Duplicate button functionality
                var duplicateBulkButton = document.querySelector('#duplicate-bulk-button');
                if (duplicateBulkButton) {
                    duplicateBulkButton.addEventListener('click', function() {
                        var selectedData = table.getSelectedData();
                        if (!selectedData.length) {
                            alert('Zaznacz rekordy do powielenia.');
                            return;
                        }

                        var entryids = selectedData.map(function(entry) {
                            return entry.id;
                        });
                        Ajax.call([{
                            methodname: 'mod_valuemapdoc_create_entries_bulk',
                            args: {
                                entryids: entryids,
                                cmid: cmid
                            }
                        }])[0].done(function(response) {
                            console.log('[tabulatormap] Entries duplicated successfully');
                            
                            // Refresh table data from database
                            reloadTableData();
                            table.deselectRow();
                        }).fail(function(error) {
                            console.error('[tabulatormap] Error duplicating entries:', error);
                            alert('Nie udało się powielić wpisów.');
                        });
                    });
                }

                // Add empty line functionality  
                var emptyBulkButton = document.querySelector('#empty-bulk-button');
                if (emptyBulkButton) {
                    emptyBulkButton.addEventListener('click', function() {
                        Ajax.call([{
                            methodname: 'mod_valuemapdoc_create_entries_bulk',
                            args: {
                                entryids: [], // Empty array for new entry
                                cmid: cmid
                            }
                        }])[0].done(function(response) {
                            console.log('[tabulatormap] Empty entry created');
                            reloadTableData();
                            table.deselectRow();
                        }).fail(function(error) {
                            console.error('[tabulatormap] Error creating empty entries:', error);
                            alert('Nie udało się utworzyć pustego wpisu.');
                        });
                    });
                }

                // Delete button functionality
                var deleteBulkButton = document.querySelector('#delete-bulk-button');
                if (deleteBulkButton) {
                    deleteBulkButton.addEventListener('click', function() {
                        var selectedData = table.getSelectedData();
                        if (!selectedData.length) {
                            alert('Zaznacz rekordy do usunięcia.');
                            return;
                        }

                        if (!confirm('Czy na pewno chcesz usunąć zaznaczone wpisy?')) {
                            return;
                        }

                        var entryids = selectedData.map(function(entry) {
                            return entry.id;
                        });
                        Ajax.call([{
                            methodname: 'mod_valuemapdoc_delete_bulk',
                            args: {
                                entryids: entryids,
                                cmid: cmid
                            }
                        }])[0].done(function(response) {
                            console.log('[tabulatormap] Entries deleted successfully');
                            reloadTableData();
                        }).fail(function(error) {
                            console.error('[tabulatormap] Error deleting entries:', error);
                            alert('Nie udało się usunąć wpisów.');
                        });
                    });
                }

                // Generate button functionality
                var generateButton = document.querySelector('#generate-button');
                if (generateButton) {
                    generateButton.addEventListener('click', function() {
                        var selectedData = table.getSelectedData();
                        if (!selectedData.length) {
                            alert('Proszę zaznaczyć co najmniej jeden rekord.');
                            return;
                        }

                        var templateSelect = document.querySelector('#templateid');
                        if (!templateSelect || !templateSelect.value) {
                            alert('Wybierz szablon przed generowaniem dokumentu.');
                            return;
                        }

                        var entryIds = selectedData.map(function(entry) {
                            return entry.id;
                        });

                        var form = document.createElement('form');
                        form.method = 'post';
                        form.action = M.cfg.wwwroot + '/mod/valuemapdoc/generate.php';
                        
                        var inputs = {
                            'id': cmid,
                            'templateid': templateSelect.value,
                            'entryids': entryIds
                        };

                        for (var name in inputs) {
                            var input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = name;
                            input.value = Array.isArray(inputs[name]) ? inputs[name].join(',') : inputs[name];
                            form.appendChild(input);
                        }

                        document.body.appendChild(form);
                        form.submit();
                    });
                }

            }).fail(function(error) {
                console.error('[tabulatormap] Error loading entries:', error);
                alert('Błąd podczas ładowania danych: ' + (error.message || 'Nieznany błąd'));
            });
        }
    };
});