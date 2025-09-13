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
        init: function() {
            console.log('[tabulatormap] Module loaded');

            var el = document.querySelector('#valuemap-table-js');
            if (!el) {
                console.warn('[tabulatormap] Table element not found');
                return;
            }

            var columns = JSON.parse(el.dataset.columns);
            var courseid = el.dataset.courseid;
            var cmid = el.dataset.cmid;
            var filtercmid = el.dataset.filtercmid || '';
            var groupfilterEl = document.querySelector('#groupfilter');
            var groupid = groupfilterEl ? groupfilterEl.value : 0;

            var fullscreenBtn = document.querySelector('#toggle-fullscreen');
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
                
                // Fallback to global user info
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
                    courseid: courseid,
                    cmid: cmid,
                    include_master: filtercmid,
                    groupid: groupid
                },
            }])[0].done(function(response) {
                console.log('[tabulatormap] Entries loaded:', response.length, 'records');

                var currentUsername = getUsernameFromResponse(response);

                var table = new Tabulator(el, {
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

                // Set default filter to show only current user's entries
                table.setHeaderFilterValue("username", currentUsername);

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

                addUserFilterToggle(table, currentUsername);

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
                    }).fail(function(error) {
                        console.error('[tabulatormap] Error updating cell:', error);
                        alert('Nie udało się zapisać zmian.');
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

                        var form = document.createElement('form');
                        form.method = 'POST';
                        form.action = generateButton.dataset.action || 'generate.php';

                        var idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id';
                        idInput.value = cmid;
                        form.appendChild(idInput);

                        var templateInput = document.createElement('input');
                        templateInput.type = 'hidden';
                        templateInput.name = 'templateid';
                        templateInput.value = templateSelect.value;
                        form.appendChild(templateInput);

                        selectedData.forEach(function(entry) {
                            var input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'entryids[]';
                            input.value = entry.id;
                            form.appendChild(input);
                        });

                        var filenameprefix = document.getElementById('filenameprefix');
                        if (filenameprefix && filenameprefix.value) {
                            var input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'filenameprefix';
                            input.value = filenameprefix.value;
                            form.appendChild(input);
                        }

                        document.body.appendChild(form);
                        form.submit();
                    });
                }

                // Bulk edit button functionality
                var editBulkButton = document.querySelector('#edit-bulk-button');
                if (editBulkButton) {
                    editBulkButton.addEventListener('click', function() {
                        var selectedData = table.getSelectedData();
                        if (!selectedData.length) {
                            alert('Zaznacz rekordy do edycji.');
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
                            methodname: 'mod_valuemapdoc_duplicate_entries',
                            args: {
                                entryids: entryids,
                                cmid: cmid
                            }
                        }])[0].done(function(response) {
                            response.forEach(function(newEntry) {
                                table.updateOrAddData([newEntry]);
                            });
                            table.deselectRow();
                        }).fail(function(error) {
                            console.error('[tabulatormap] Error duplicating entries:', error);
                            alert('Nie udało się powielić wpisów.');
                        });
                    });
                }

                // Empty button functionality  
                var emptyBulkButton = document.querySelector('#empty-bulk-button');
                if (emptyBulkButton) {
                    emptyBulkButton.addEventListener('click', function() {
                        var selectedData = table.getSelectedData();
                        if (!selectedData.length) {
                            var selector = document.querySelector('#bulk-actions');
                            var button = selector ? selector.querySelector('button') : null;
                            if (button) {
                                button.disabled = true;
                                setTimeout(function() {
                                    button.removeAttribute('disabled');
                                }, 3000);
                            }
                            if (selector) {
                                selector.classList.add('animate');
                                setTimeout(function() {
                                    selector.classList.remove('animate');
                                }, 1000);
                            }
                            return;
                        }

                        var entryids = selectedData.map(function(entry) {
                            return entry.id;
                        });
                        Ajax.call([{
                            methodname: 'mod_valuemapdoc_empty_entries',  
                            args: {
                                entryids: entryids,
                                cmid: cmid
                            }
                        }])[0].done(function(response) {
                            response.forEach(function(updatedEntry) {
                                table.updateOrAddData([updatedEntry]);
                            });
                            table.deselectRow();
                        }).fail(function(error) {
                            console.error('[tabulatormap] Error emptying entries:', error);
                            alert('Nie udało się wyczyścić wpisów.');
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

                        var filteredData = selectedData.filter(function(entry) {
                            return !entry.blocked;
                        });
                        if (filteredData.length !== selectedData.length) {
                            alert('Niektóre wpisy nie mogą być usunięte.');
                            return;
                        }

                        if (!confirm('Czy na pewno chcesz usunąć zaznaczone wpisy?')) {
                            return;
                        }

                        var entryids = selectedData.map(function(entry) {
                            return entry.id;
                        });
                        Ajax.call([{
                            methodname: 'mod_valuemapdoc_delete_entries',
                            args: {
                                entryids: entryids,
                                cmid: cmid
                            }
                        }])[0].done(function(response) {
                            entryids.forEach(function(id) {
                                table.deleteRow(id);
                            });
                        }).fail(function(error) {
                            console.error('[tabulatormap] Error deleting entries:', error);
                            alert('Nie udało się usunąć wpisów.');
                        });
                    });
                }

                // Move to master button functionality
                var moveBulkButton = document.querySelector('#move-bulk-button');
                if (moveBulkButton) {
                    moveBulkButton.addEventListener('click', function() {
                        var selectedData = table.getSelectedData();
                        if (!selectedData.length) {
                            alert('Zaznacz rekordy do przeniesienia.');
                            return;
                        }

                        Ajax.call([{
                            methodname: 'mod_valuemapdoc_get_master_instances',
                            args: {
                                courseid: courseid
                            }
                        }])[0].done(function(instances) {
                            if (!instances.length) {
                                alert('Brak dostępnych instancji master.');
                                return;
                            }

                            var targetcmid;
                            if (instances.length === 1) {
                                targetcmid = instances[0].cmid;
                            } else {
                                // Show selection dialog for multiple instances
                                // Implementation would go here
                                return;
                            }

                            var entryids = selectedData.map(function(entry) {
                                return entry.id;
                            });
                            Ajax.call([{
                                methodname: 'mod_valuemapdoc_move_entries_to_master',
                                args: {
                                    entryids: entryids,
                                    targetcmid: targetcmid
                                }
                            }])[0].done(function(response) {
                                entryids.forEach(function(id) {
                                    table.deleteRow(id);
                                });
                            }).fail(function(error) {
                                console.error('[tabulatormap] Error moving entries:', error);
                                alert('Nie udało się przenieść wpisów.');
                            });
                        });
                    });
                }

                // Group filter functionality
                var groupFilter = document.querySelector('#groupfilter');
                if (groupFilter) {
                    groupFilter.addEventListener('change', function() {
                        var selectedGroupId = this.value;
                        var masterFilterEl = document.querySelector('#masterfilter');
                        var currentFilterCmid = masterFilterEl ? masterFilterEl.value : '';
                        var currentUsernameFilter = table.getHeaderFilterValue('username') || '';

                        Ajax.call([{
                            methodname: 'mod_valuemapdoc_get_entries',
                            args: {
                                courseid: parseInt(courseid),
                                cmid: parseInt(cmid),
                                include_master: currentFilterCmid,
                                groupid: parseInt(selectedGroupId)
                            }
                        }])[0].done(function(newResponse) {
                            table.setData(newResponse);
                            if (currentUsernameFilter) {
                                table.setHeaderFilterValue('username', currentUsernameFilter);
                            }
                        }).catch(function(error) {
                            console.error('[tabulatormap] Error loading group data:', error);
                        });
                    });
                }

                // Master filter functionality
                var masterFilter = document.querySelector('#masterfilter');
                if (masterFilter) {
                    masterFilter.addEventListener('change', function() {
                        var newFilterCmid = this.value;
                        
                        // Save user preference for master filter
                        Ajax.call([{
                            methodname: 'core_user_set_user_preferences',
                            args: {
                                preferences: [{
                                    name: 'mod_valuemapdoc_master_filter',
                                    value: newFilterCmid
                                }]
                            }
                        }]).then(function() {
                            // Reload page with new filter
                            var url = new URL(window.location);
                            if (newFilterCmid) {
                                url.searchParams.set('filtercmid', newFilterCmid);
                            } else {
                                url.searchParams.delete('filtercmid');
                            }
                            window.location.href = url.toString();
                        }).catch(function(error) {
                            console.error('[tabulatormap] Error saving filter preference:', error);
                            // Still reload even if preference saving failed
                            var url = new URL(window.location);
                            if (newFilterCmid) {
                                url.searchParams.set('filtercmid', newFilterCmid);
                            } else {
                                url.searchParams.delete('filtercmid');
                            }
                            window.location.href = url.toString();
                        });
                    });
                }

                // Redraw table after initialization
                table.redraw();

            }).fail(function(error) {
                console.error('[tabulatormap] Error loading entries:', error);
                alert('Nie udało się załadować danych.');
            });
        }
    };
});