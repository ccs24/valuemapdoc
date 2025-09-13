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
    
    /**
     * Hierarchy Selector functionality integrated into tabulatormap
     */
    var HierarchySelector = {
        cmid: null,
        
        init: function(cmid) {
            this.cmid = cmid;
            this.setupEventListeners();
            this.initializeState();
        },

        setupEventListeners: function() {
            var self = this;
            
            // Market selector change
            $(document).on('change', 'select[data-hierarchy-level="market"]', function() {
                var marketId = $(this).val();
                self.loadChildren(marketId, 'customer');
                self.clearChildSelectors(['customer', 'person', 'opportunity']);
            });

            // Customer selector change  
            $(document).on('change', 'select[data-hierarchy-level="customer"]', function() {
                var customerId = $(this).val();
                if (customerId > 0) {
                    self.loadChildren(customerId, 'person');
                    self.loadChildren(customerId, 'opportunity');
                } else {
                    self.clearChildSelectors(['person', 'opportunity']);
                }
            });

            // Person and Opportunity selector changes
            $(document).on('change', 'select[data-hierarchy-level="person"], select[data-hierarchy-level="opportunity"]', function() {
                self.updateSummary();
            });

            // Reset button click
            $(document).on('click', '#hierarchy-reset-button', function() {
                self.reset();
            });
        },

        initializeState: function() {
                        console.log('initializeState', this.cmid);

            // Load initial markets
            this.loadInitialMarkets();
            
            // Show/hide selectors based on current values
            this.updateVisibility();
            
            // Update summary
            this.updateSummary();
        },

        loadInitialMarkets: function() {
            var self = this;
            var marketSelector = $('select[data-hierarchy-level="market"]');
            console.log('lenghts:' .marketSelector.length );
            
            if (marketSelector.length === 0) {
//                return;
            }

            // Load markets via AJAX
            var request = {
                methodname: 'mod_valuemapdoc_get_markets',
                args: {
                    cmid: this.cmid,
                    parentid: 0, // Root level for markets
                    childtype: 'market'
                }
            };

            console.log(`[Load markets] Fetching data for market cmid=${this.cmid}, parent=0`);

            Ajax.call([request])[0].done(function(response) {
                console.log('🔍 Initial markets loaded:', response);
                if (response.success && response.options) {
                    // Update market selector with real options
                    marketSelector.empty();
                    response.options.forEach(function(option) {
                        marketSelector.append('<option value="' + option.value + '">' + option.text + '</option>');
                    });
                }
            }).fail(function(error) {
                console.error('Error loading initial markets:', error);
                // Keep default placeholder option
            });
        },

        loadChildren: function(parentId, childType) {
            var self = this;
            var selector = $('select[data-hierarchy-level="' + childType + '"]');
            
            if (selector.length === 0) {
                return Promise.resolve();
            }

            // Show loading state
            selector.prop('disabled', true);
            selector.html('<option value="0">Ładowanie...</option>');

            var request = {
                methodname: 'mod_valuemapdoc_get_markets',
                args: {
                    cmid: this.cmid,
                    parentid: parseInt(parentId),
                    childtype: childType
                }
            };

            console.log(`[Load children] Fetching data for ${childType}, cmid=${this.cmid}, parent=${parentId}`);


            return Ajax.call([request])[0].done(function(response) {
                console.log('🔍 Loaded children for', childType, ':', response);
                if (response.success) {
                    self.updateSelectorOptions(selector, response.options);
                    selector.prop('disabled', false);
                    self.updateVisibility();
                    return response;
                } else {
                    throw new Error('Failed to load ' + childType + ' options');
                }
            }).fail(function(error) {
                console.error('Error loading children:', error);
                Notification.addNotification({
                    message: 'Error loading ' + childType + ' options: ' + error.message,
                    type: 'error'
                });
                selector.prop('disabled', false);
                selector.html('<option value="0">-- Wybierz --</option>');
                throw error;
            });
        },

        updateSelectorOptions: function(selector, options) {
            selector.empty();
            
            options.forEach(function(option) {
                var optionElement = $('<option></option>')
                    .attr('value', option.value)
                    .text(option.text);
                
                if (option.selected) {
                    optionElement.prop('selected', true);
                }
                
                selector.append(optionElement);
            });
        },

        clearChildSelectors: function(levels) {
            var self = this;
            
            levels.forEach(function(level) {
                var selector = $('select[data-hierarchy-level="' + level + '"]');
                if (selector.length > 0) {
                    selector.empty();
                    var placeholder = '-- ' + level.charAt(0).toUpperCase() + level.slice(1) + ' --';
                    selector.append('<option value="0">' + placeholder + '</option>');
                    selector.val(0);
                }
            });
            
            this.updateVisibility();
        },

        updateVisibility: function() {
            var marketValue = $('select[data-hierarchy-level="market"]').val() || 0;
            var customerValue = $('select[data-hierarchy-level="customer"]').val() || 0;
            var resetButton = $('#hierarchy-reset-button');

            // Customer selector
            var customerContainer = $('select[data-hierarchy-level="customer"]').closest('.input-group');
            if (marketValue > 0) {
                customerContainer.show();
            } else {
                customerContainer.hide();
            }

            // Person and Opportunity selectors
            var personContainer = $('select[data-hierarchy-level="person"]').closest('.input-group');
            var opportunityContainer = $('select[data-hierarchy-level="opportunity"]').closest('.input-group');
            
            if (customerValue > 0) {
                personContainer.show();
                opportunityContainer.show();
            } else {
                personContainer.hide();
                opportunityContainer.hide();
            }

            // Reset button - show if any selection is made
            if (marketValue > 0 || customerValue > 0) {
                resetButton.show();
            } else {
                resetButton.hide();
            }

        },


        updateSummary: function() {
            var summary = [];
            var icons = {
                'market': '🔍',
                'customer': '🏛',
                'person': '👤', 
                'opportunity': '💰'
            };

            ['market', 'customer', 'person', 'opportunity'].forEach(function(level) {
                var selector = $('select[data-hierarchy-level="' + level + '"]');
                var value = selector.val();
                var text = selector.find('option:selected').text();
                
                if (value && value > 0 && text !== '-- Wybierz --' && text !== 'Ładowanie...') {
                    summary.push(icons[level] + ' ' + text);
                }
            });

            var summaryElement = $('.hierarchy-selector-summary');
            if (summaryElement.length > 0) {
                if (summary.length > 0) {
                    summaryElement.html('<strong>Wybrano:</strong> ' + summary.join(' → ')).show();
                } else {
                    summaryElement.hide();
                }
            }
        },

        getCurrentSelection: function() {
            return {
                market: $('select[data-hierarchy-level="market"]').val() || 0,
                customer: $('select[data-hierarchy-level="customer"]').val() || 0,
                person: $('select[data-hierarchy-level="person"]').val() || 0,
                opportunity: $('select[data-hierarchy-level="opportunity"]').val() || 0
            };
        },

        validateSelection: function() {
            var selection = this.getCurrentSelection();
            var errors = [];

            if (selection.customer > 0 && selection.market == 0) {
                errors.push('Market must be selected when customer is selected');
            }

            if (selection.person > 0 && selection.customer == 0) {
                errors.push('Customer must be selected when person is selected');
            }

            if (selection.opportunity > 0 && selection.customer == 0) {
                errors.push('Customer must be selected when opportunity is selected');
            }

            return {
                valid: errors.length === 0,
                errors: errors,
                selection: selection
            };
        },

        reset: function() {
            $('select[data-hierarchy-level]').each(function() {
                $(this).val(0);
            });
            this.clearChildSelectors(['customer', 'person', 'opportunity']);
            this.updateVisibility();
            this.updateSummary();
        },

        // Get selected hierarchy data for document generation
        getSelectedHierarchyData: function() {
            var selection = this.getCurrentSelection();
            var data = {};
            
            // Add selected values to generation data
            if (selection.market > 0) {data.hierarchy_market = selection.market;}
            if (selection.customer > 0) {data.hierarchy_customer = selection.customer;}
            if (selection.person > 0) {data.hierarchy_person = selection.person;}
            if (selection.opportunity > 0) {data.hierarchy_opportunity = selection.opportunity;}
            
            return data;
        }
    };

    return {
        init: function() {
            console.log('[tabulatormap] Module loaded');
            // --- Hierarchy selectors (Moodle 4.5+ AJAX) ---
            /**
             * Initializes hierarchy selectors for market, customer, person, and opportunity.
             */
            function initHierarchySelectors() {
                console.log('[initHierarchySelectors] Initializing hierarchy selectors...');
                const levels = ['market', 'customer', 'person', 'opportunity'];
                // Patch: Only update market select if element exists and cmid is available.
                const marketSelect = document.querySelector('#hierarchy_market');
                if (marketSelect) {
                    const localCmid = parseInt(marketSelect.dataset.cmid, 10);
                    console.log(`[initHierarchySelectors] Forcing market fetch for cmid=${localCmid}`);
                    updateSelect('market', 0, localCmid);
                } else {
                    console.warn('[initHierarchySelectors] Cannot find #hierarchy_market element.');
                }

                /**
                 * Capitalizes the first letter of a string.
                 * @param {string} str - The input string.
                 * @returns {string} The capitalized string.
                 */
                function capitalize(str) {
                    return str.charAt(0).toUpperCase() + str.slice(1);
                }

                /**
                 * Handles change event for a hierarchy level select.
                 * @param {string} level - The level of the hierarchy (e.g., 'market').
                 */
                // --- Hierarchy dependency map (for multiple children per parent) ---
                // Map each hierarchy level to an array of next levels
                const hierarchyMap = {
                    market: ['customer'],
                    customer: ['person', 'opportunity'],
                    person: [],
                    opportunity: []
                };

                // Map of parallel levels for each parent type
                const parallelLevels = {
                    customer: ['person', 'opportunity']
                };

                /**
                 * Handles selection changes in hierarchy dropdowns and loads related child data.
                 *
                 * @param {string} currentLevel - The current level of the selector hierarchy.
                 */
                function handleChange(currentLevel) {
                    const index = levels.indexOf(currentLevel);
                    const $current = $('#hierarchy_' + currentLevel);
                    const selectedId = $current.val();
                    // Ensure cmid is defined and in scope
                    let cmid = $current.data('cmid');
                    if (!cmid) {
                        // Try to get from #hierarchy_market if not found
                        const marketEl = document.querySelector('#hierarchy_market');
                        if (marketEl && marketEl.dataset.cmid) {
                            cmid = parseInt(marketEl.dataset.cmid, 10);
                        }
                    }
                    // Debug log for cmid
                    console.log('[AJAX DEBUG] cmid before call:', cmid);

                    // Clear all lower-level selects, but do not clear parallel levels of current parent
                    // Only clear levels that are descendants and not parallel children
                    let toClear = levels.filter(level => {
                        return levels.indexOf(level) > index
                            && !(parallelLevels[currentLevel] || []).includes(level);
                    });
                    toClear.forEach(nextLevel => {
                        const $lower = $('#hierarchy_' + nextLevel);
                        $lower.parent().hide();
                        // Clear lower selects
                        $lower.empty();
                        $lower.append($('<option>').val('0').text('-- ' + capitalize(nextLevel) + ' --'));
                    });

                    if (selectedId === '0' || !hierarchyMap[currentLevel] || hierarchyMap[currentLevel].length === 0) {
                        return;
                    }

                    // For each next level, load its data (can be more than one, e.g. for 'customer')
                    const nextLevels = hierarchyMap[currentLevel] || [];
                    nextLevels.forEach(level => {
                        const $next = $('#hierarchy_' + level);
                        Ajax.call([{
                            methodname: 'mod_valuemapdoc_get_markets',
                            args: {
                                cmid: parseInt(cmid, 10),
                                parentid: parseInt(selectedId, 10),
                                childtype: level
                            }
                        }])[0].done(function(result) {
                            console.log('[AJAX] Monitor received data for', level, result);
                            if (result && result.options && result.options.length > 1) {
                                $next.empty();
                                $next.append($('<option>').val('0').text('-- ' + capitalize(level) + ' --'));
                                result.options.slice(1).forEach(opt => {
                                    $next.append($('<option>').val(opt.value).text(opt.text));
                                });
                                $next.parent().show();
                            }
                        }).fail(function(error) {
                            console.error('[AJAX] Monitor error for', level, error);
                            Notification.exception(error);
                        });
                    });
                }

                $('#hierarchy-reset-button').on('click', function() {
                    levels.forEach(function(level, index) {
                        const $sel = $('#hierarchy_' + level);
                        $sel.val('0');
                        if (index > 0) {
                            $sel.parent().hide();
                        }
                    });
                });

                // Only attach change handlers for 'market' and 'customer'.
                ['market', 'customer'].forEach(function(level) {
                    const $select = $('#hierarchy_' + level);
                    if ($select.length) {
                        $select.on('change', function() {
                            handleChange(level);
                        });
                    }
                });
            }

            /**
             * Loads child data for a given hierarchy level.
             *
             * @param {string} type - The type of the child level (e.g. 'person').
             * @param {number} parentid - The ID of the parent.
             * @param {number} cmid - The course module ID.
             */
            function updateSelect(type, parentid, cmid) {
                const select = document.querySelector(`#hierarchy_${type}`);
                if (!select) {
                    console.warn(`[updateSelect] Missing select element for #hierarchy_${type}`);
                    return;
                }

                // Clear previous options
                select.innerHTML = '';

                // Use the core/ajax module to make the request
                console.log('[AJAX] Requesting data (monitor) for type:', type, 'parentid:', parentid, 'cmid:', cmid);
                Ajax.call([{
                    methodname: 'mod_valuemapdoc_get_markets',
                    args: {
                        cmid: cmid,
                        parentid: parseInt(parentid, 10),
                        childtype: type
                    }
                }])[0].done(function(result) {
                    console.log('[AJAX] Monitor received data for', type, result);
                    if (!result || !result.options) {
                        console.warn(`[updateSelect] No options returned for ${type}`);
                        return;
                    }

                    // Populate the select with new options
                    result.options.forEach((opt) => {
                        const option = document.createElement('option');
                        option.value = opt.value;
                        option.textContent = opt.text;
                        if (opt.selected) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });

                    // Show the select if it was hidden
                    if (select.parentElement) {
                        select.parentElement.style.display = '';
                    }
                }).fail(function(error) {
                    console.error('[AJAX] Monitor error for', type, error);
                    Notification.exception(error);
                });
            }

            // --- END Hierarchy selectors ---
            initHierarchySelectors();
            console.log('HierarchySelector INIT1');

            const el = document.querySelector('#valuemap-table-js');
            const columns = JSON.parse(el.dataset.columns);
            const courseid = el.dataset.courseid;
            const cmid = el.dataset.cmid;
            const filtercmid = el.dataset.filtercmid || '';
            const groupid = document.querySelector('#groupfilter')?.value || 0;

            // Initialize Hierarchy Selector if present
            const hierarchyContainer = document.querySelector('.hierarchy-selector, .hierarchy-selector-compact');
            if (hierarchyContainer) {
                HierarchySelector.init(parseInt(cmid));
                console.log("🔍 Hierarchy Selector initialized for cmid:", cmid);
            }

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
             * Wyświetla okno dialogowe z listą instancji Master do wyboru.
             * @param {Array} instances Lista instancji Master z polami cmid i name
             * @param {Function} callback Funkcja wywoływana po wyborze z parametrem selectedId
             */
            function showSelectDialog(instances, callback) {
                const dialog = document.createElement('div');
                dialog.innerHTML = `
                    <label>Wybierz aktywność Master:</label><br>
                    <select id="master-select" style="margin-top: 8px; width: 100%;">
                        ${instances.map(i => `<option value="${i.cmid}">${i.name}</option>`).join("")}
                    </select>
                    <div style="margin-top: 12px; text-align: right;">
                        <button id="master-cancel">Anuluj</button>
                        <button id="master-confirm">OK</button>
                    </div>
                `;
                dialog.style = "position: fixed; top: 20%; left: 50%; transform: translateX(-50%); padding: 20px; background: white; border: 1px solid #ccc; z-index: 9999;";
                document.body.appendChild(dialog);

                dialog.querySelector('#master-cancel').onclick = () => {
                    document.body.removeChild(dialog);
                };
                dialog.querySelector('#master-confirm').onclick = () => {
                    const selectedId = parseInt(dialog.querySelector('#master-select').value);
                    document.body.removeChild(dialog);
                    callback(selectedId);
                };
            }

            /**
             * Generate Tabulator columns with editable fields and action buttons.
             * @param {Array} columns Array of column definitions received from dataset
             * @returns {Array} Enhanced column definitions with selection and action buttons
             */
            function prepareColumns(columns) {
                return [
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
                    }))
                ];
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
                console.log('🔍 Entries loaded:', response);

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
                            // Success
                        }).fail(function(error) {
                            console.error('❌ Błąd:', error);
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

                // Event delegation for action buttons
                el.addEventListener('click', function(event) {
                    const editBtn = event.target.closest('.btn-edit');
                    const deleteBtn = event.target.closest('.btn-delete');
                    const moveBtn = event.target.closest('.btn-move');

                    if (editBtn) {
                        const id = editBtn.dataset.id;
                        window.location.href = `edit.php?id=${cmid}&entryid=${id}`;
                    }

                    if (deleteBtn) {
                        const id = deleteBtn.dataset.id;
                        const entryid = parseInt(id, 10);
                        const cmidInt = parseInt(cmid, 10);
                        if (confirm("Czy na pewno chcesz usunąć ten wpis?")) {
                            Ajax.call([{
                                methodname: 'mod_valuemapdoc_delete_entry',
                                args: {
                                    entryid: entryid,
                                    cmid: cmidInt
                                },
                            }])[0].done(function() {
                                table.deleteRow(entryid);
                            }).fail(function(error) {
                                console.error("❌ Błąd usuwania wpisu:", error);
                                alert("Nie udało się usunąć wpisu.");
                            });
                        }
                    }

                    if (moveBtn) {
                        const id = moveBtn.dataset.id;
                        if (confirm("Czy na pewno chcesz przenieść ten wpis do master?")) {
                            Ajax.call([{
                                methodname: 'mod_valuemapdoc_get_master_instances',
                                args: { courseid: courseid },
                            }])[0].done(function(instances) {
                                if (instances.length === 0) {
                                    alert("Brak dostępnych aktywności typu Master.");
                                    return;
                                }

                                if (instances.length > 1) {
                                    showSelectDialog(instances, function(selectedId) {
                                        Ajax.call([{
                                            methodname: 'mod_valuemapdoc_move_entry_to_master',
                                            args: {
                                                entryid: id,
                                                targetcmid: selectedId
                                            },
                                        }])[0].done(function() {
                                            table.deleteRow(id);
                                        }).fail(function(error) {
                                            alert("Nie udało się przenieść wpisu.");
                                        });
                                    });
                                } else {
                                    const selectedId = instances[0].cmid;
                                    Ajax.call([{
                                        methodname: 'mod_valuemapdoc_move_entry_to_master',
                                        args: {
                                            entryid: id,
                                            targetcmid: selectedId
                                        },
                                    }])[0].done(function() {
                                        table.deleteRow(id);
                                    }).fail(function(error) {
                                        alert("Nie udało się przenieść wpisu.");
                                    });
                                }
                            }).fail(function(error) {
                                alert("Nie udało się pobrać aktywności Master.");
                            });
                        }
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

                // ENHANCED GENERATE BUTTON - includes hierarchy data
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

                        // Validate hierarchy selection if hierarchy selector is present
                        if (hierarchyContainer) {
                            const validation = HierarchySelector.validateSelection();
                            if (!validation.valid && validation.errors.length > 0) {
                                alert("Błąd w wyborze hierarchii:\n" + validation.errors.join('\n'));
                                return;
                            }
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

                        // Add market hierarchy inputs
                        const levels = ['market', 'customer', 'person', 'opportunity'];
                        levels.forEach(level => {
                            const select = document.getElementById(`hierarchy_${level}`);
                            if (select && select.value && select.value !== "0") {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = `${level}id`;
                                input.value = select.value;
                                form.appendChild(input);
                            }
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

                        // ADD HIERARCHY DATA if hierarchy selector is present
                        if (hierarchyContainer) {
                            const hierarchyData = HierarchySelector.getSelectedHierarchyData();
                            Object.keys(hierarchyData).forEach(key => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = key;
                                input.value = hierarchyData[key];
                                form.appendChild(input);
                            });
                        }

                        // Show spinner
                        const spinner = document.querySelector('#spinner-indicator');
                        if (spinner) {
                            spinner.classList.remove('d-none');
                            sessionStorage.setItem('valuemapdoc_pending_generation', '1');
                            sessionStorage.setItem('valuemapdoc_pending_timestamp', Date.now().toString());
                        }

                        document.body.appendChild(form);
                        form.submit();
                    });
                }

                // Bulk operations handlers (pozostałe bez zmian)
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
                            console.error("❌ Błąd powielania wielu rekordów:", error);
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
                            console.error("❌ Błąd tworzenia pustych rekordów:", error);
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
                                console.error("❌ Błąd usuwania wielu rekordów:", error);
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

                            showSelectDialog(instances, function(targetcmid) {
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
                                    console.error("❌ Błąd usuwania wielu rekordów:", error);
                                    alert("Nie udało się przenieść rekordów.");
                                });
                            });
                        }).fail((error) => {
                            console.error("❌ Błąd pobierania instancji Master:", error);   
                            alert("Nie udało się pobrać listy Master Map.");
                        });
                    });
                }

                // Export handler
                const exportButton = document.querySelector('#export-button');
                if (exportButton) {
                    exportButton.addEventListener('click', function () {
                        const selectedData = table.getSelectedData();
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = exportButton.dataset.action || 'export.php';

                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id';
                        idInput.value = cmid;
                        form.appendChild(idInput);

                        if (selectedData.length > 0) {
                            selectedData.forEach(entry => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'entryids[]';
                                input.value = entry.id;
                                form.appendChild(input);
                            });
                        }

                        document.body.appendChild(form);
                        form.submit();
                    });
                }

                // Window resize handler
                window.addEventListener('resize', () => {
                    table.redraw(true);
                });

                // Group filter handler
                const groupFilter = document.querySelector('#groupfilter');
                if (groupFilter) {
                    groupFilter.addEventListener('change', () => {
                        const selectedGroupId = groupFilter.value;
                        const currentFilterCmid = masterFilter?.value || filtercmid;

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
                        }).fail(function(error) {
                            console.error("❌ Błąd ładowania nowych danych po zmianie grupy:", error);
                            alert("❌ AJAX reload error (group):", error);
                        });
                    });
                }

                // Master filter handler
                const masterFilter = document.querySelector('#masterfilter');
                if (masterFilter) {
                    masterFilter.addEventListener('change', () => {
                        const newFilterCmid = masterFilter.value;

                        // Save user preference
                        UserRepository.setUserPreference('mod_valuemapdoc_masterfilters', cmid + ':' + newFilterCmid)
                            .then(() => {
                                console.log("✅ Preferencja zapisana:", newFilterCmid);
                            })
                            .catch(error => {
                                console.error("❌ Błąd zapisu preferencji:", error);
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
                        }).fail(function(error) {
                            console.error("❌ Błąd ładowania nowych danych:", error);
                            alert("❌ AJAX reload error:", error);
                        });
                    });
                }

                // Add hierarchy reset button handler if present
                const hierarchyResetButton = document.querySelector('#hierarchy-reset-button');
                if (hierarchyResetButton && hierarchyContainer) {
                    hierarchyResetButton.addEventListener('click', function() {
                        HierarchySelector.reset();
                    });
                }

            }).fail(function(error) {
                alert("Nie udało się załadować danych.");
                console.error("❌ AJAX error:", error);
            });
        },

        // Expose hierarchy selector for external use
        getHierarchySelector: function() {
            return HierarchySelector;
        }
    };
});