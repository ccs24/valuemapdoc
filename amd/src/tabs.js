// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Lightweight tabs handler for Moodle plugin.
 *
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    return {
        /**
         * Initialize tab switching based on URL hash and clicks.
         */
        init: function() {
            /**
             * Shows the selected tab and activates the corresponding navigation link.
             *
             * @param {string} hash - The ID of the tab to activate (including #).
             */
            function showTab(hash) {
                document.querySelectorAll('#valuemapdoc-tabs .tab-pane').forEach(function(tab) {
                    tab.classList.remove('active', 'show');
                });
                document.querySelectorAll('#valuemapdoc-tabs .nav-link').forEach(function(link) {
                    link.classList.remove('active');
                });

                const activeTab = document.querySelector(hash);
                const activeLink = document.querySelector('[data-href="' + hash + '"]');

                if (activeTab) {
                    activeTab.classList.add('active', 'show');
                }
                if (activeLink) {
                    activeLink.classList.add('active');
                }
            }

            document.querySelectorAll('#valuemapdoc-tabs .nav-link').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetHash = this.getAttribute('data-href');
                    if (targetHash) {
                        if (targetHash === '#content-tab') {
                            import('mod_valuemapdoc/tablecontent').then((module) => {
                                module.init();

                                const spinner = document.querySelector('#spinner-indicator');
                                const isPending = sessionStorage.getItem('valuemapdoc_pending_generation') === '1';
                                const startTime = Date.now();

                                if (spinner && isPending) {
                                    spinner.classList.remove('d-none');
                                }

                                /**
                                 * Polls the server every 5 seconds to check if any content document has status 'ready'.
                                 * Hides spinner once a document is ready or after a timeout.
                                 */
                                function checkDocumentsReady() {
                                    require(['core/ajax'], function(Ajax) {
                                        const container = document.querySelector('#valuemapdoc-content-table');
                                        if (!container) { return; }

                                        const courseid = container.dataset.courseid;
                                        const cmid = container.dataset.cmid;

                                        Ajax.call([{
                                            methodname: 'mod_valuemapdoc_get_content_entries',
                                            args: {
                                                courseid: courseid,
                                                cmid: cmid,
                                                include_master: 0
                                            }
                                        }])[0].then(function(entries) {
                                            const ready = entries.length && entries.some(e => e.status === 'ready');
                                            const expired = (Date.now() - startTime > 20000); // 20s timeout

                                            if (ready || expired) {
                                                spinner?.classList.add('d-none');
                                                sessionStorage.removeItem('valuemapdoc_pending_generation');
                                            } else {
                                                setTimeout(checkDocumentsReady, 5000);
                                            }
                                        });
                                    });
                                }

                                if (isPending) {
                                    checkDocumentsReady();
                                }

                                showTab(targetHash);
                            });
                        } else {
                            history.pushState(null, null, targetHash);
                            showTab(targetHash);
                        }
                    }
                });
            });

            if (window.location.hash) {
                showTab(window.location.hash);
            } else {
                const firstTabLink = document.querySelector('#valuemapdoc-tabs .nav-link[data-href]');
                if (firstTabLink) {
                    const firstHash = firstTabLink.getAttribute('data-href');
                    if (firstHash) {
                        history.replaceState(null, null, firstHash);
                        showTab(firstHash);
                    }
                }
            }

            window.addEventListener('hashchange', function() {
                if (window.location.hash) {
                    showTab(window.location.hash);
                }
            });
        }
    };
});