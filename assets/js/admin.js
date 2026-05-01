document.addEventListener('DOMContentLoaded', function () {
    const triggerBtn = document.querySelector('.detit-trigger');
    const container = document.getElementById('detit-container');
    
    if (!triggerBtn || typeof detitData === 'undefined') return;

    let productId = triggerBtn.getAttribute('data-product-id');

    // Create Modal HTML and append to body
    const modalHTML = `
        <div id="detit-modal-overlay" class="detit-modal-overlay">
            <div class="detit-modal">
                <div class="detit-modal-header">
                    <h2>Generated Content Preview</h2>
                    <button type="button" class="detit-close-btn" id="detit-close-modal">&times;</button>
                </div>
                <div class="detit-modal-body" id="detit-modal-body">
                    <!-- Fields will be populated here -->
                </div>
                <div class="detit-modal-footer">
                    <button type="button" id="detit-add-all-btn" class="detit-action-btn">Add All Fields</button>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    const modalOverlay = document.getElementById('detit-modal-overlay');
    const modalBody = document.getElementById('detit-modal-body');
    const closeModalBtn = document.getElementById('detit-close-modal');
    const addAllBtn = document.getElementById('detit-add-all-btn');
    const responseBox = document.getElementById('detit-response');

    // Handle Generation
    triggerBtn.addEventListener('click', function () {
        triggerBtn.disabled = true;
        triggerBtn.textContent = 'Processing...';
        
        let spinner = document.createElement('span');
        spinner.className = 'detit-spinner';
        spinner.id = 'detit-spinner';
        triggerBtn.parentNode.insertBefore(spinner, triggerBtn.nextSibling);

        const formData = new URLSearchParams();
        formData.append('action', 'detit_generate');
        formData.append('product_id', productId);
        formData.append('nonce', detitData.nonce);

        fetch(detitData.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            if (res.success && res.data.result) {
                populateModal(res.data.result);
                openModal();
            } else {
                responseBox.innerHTML = `<p style="color:red;">${res.data?.message || 'Error generating content'}</p>`;
            }
        })
        .catch(err => {
            responseBox.innerHTML = '<p style="color:red;">AJAX error occurred</p>';
        })
        .finally(() => {
            triggerBtn.disabled = false;
            triggerBtn.textContent = 'Detail It';
            if (document.getElementById('detit-spinner')) {
                document.getElementById('detit-spinner').remove();
            }
        });
    });

    function flattenObject(ob) {
        let toReturn = {};
        for (let i in ob) {
            if (!ob.hasOwnProperty(i)) continue;
            if ((typeof ob[i]) === 'object' && ob[i] !== null && !Array.isArray(ob[i])) {
                let flatObject = flattenObject(ob[i]);
                for (let x in flatObject) {
                    if (!flatObject.hasOwnProperty(x)) continue;
                    toReturn[i + '.' + x] = flatObject[x];
                }
            } else {
                toReturn[i] = ob[i];
            }
        }
        return toReturn;
    }

    function populateModal(data) {
        modalBody.innerHTML = '';
        const flatData = flattenObject(data);

        for (const [key, value] of Object.entries(flatData)) {
            let stringValue = Array.isArray(value) ? value.join(', ') : value;
            if (stringValue === null || stringValue === undefined) stringValue = '';
            
            // Escape stringValue to prevent XSS in HTML
            let escapedValue = String(stringValue)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");

            let isTextarea = escapedValue.length > 50;

            let rowHTML = `
                <div class="detit-field-row" data-field-name="${key}">
                    <label>${key.replace(/_/g, ' ').replace(/\./g, ' - ')}</label>
                    <div class="detit-input-wrapper">
                        ${isTextarea 
                            ? `<textarea class="detit-textarea" data-field="${key}">${escapedValue}</textarea>` 
                            : `<input type="text" class="detit-input" data-field="${key}" value="${escapedValue}">`
                        }
                        <button type="button" class="detit-action-btn detit-add-single-btn" data-field="${key}">Add</button>
                    </div>
                    <div class="detit-feedback"></div>
                </div>
            `;
            modalBody.insertAdjacentHTML('beforeend', rowHTML);
        }

        // Attach event listeners to single add buttons
        document.querySelectorAll('.detit-add-single-btn').forEach(btn => {
            btn.addEventListener('click', handleSingleAdd);
        });
        
        addAllBtn.disabled = false;
        addAllBtn.textContent = 'Add All Fields';
        addAllBtn.classList.remove('detit-success');
    }

    function openModal() {
        modalOverlay.classList.add('detit-open');
    }

    function closeModal() {
        modalOverlay.classList.remove('detit-open');
    }

    closeModalBtn.addEventListener('click', closeModal);

    // Click outside to close
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) closeModal();
    });

    function handleSingleAdd(e) {
        const btn = e.target;
        const fieldName = btn.getAttribute('data-field');
        const row = btn.closest('.detit-field-row');
        const input = row.querySelector(`[data-field="${fieldName}"]`);
        const feedback = row.querySelector('.detit-feedback');

        btn.disabled = true;
        btn.textContent = '...';

        const formData = new URLSearchParams();
        formData.append('action', 'detit_add_single_field');
        formData.append('product_id', productId);
        formData.append('field', fieldName);
        formData.append('value', input.value);
        formData.append('nonce', detitData.nonce);

        fetch(detitData.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                btn.textContent = '✓';
                btn.classList.add('detit-success');
                feedback.textContent = '';
                feedback.classList.remove('detit-error');
                updateMainPageField(fieldName, input.value);
            } else {
                btn.disabled = false;
                btn.textContent = 'Add';
                feedback.textContent = res.data?.message || 'Error updating field';
                feedback.classList.add('detit-error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.textContent = 'Add';
            feedback.textContent = 'AJAX error occurred';
            feedback.classList.add('detit-error');
        });
    }

    addAllBtn.addEventListener('click', function() {
        const fields = {};
        document.querySelectorAll('.detit-field-row').forEach(row => {
            const input = row.querySelector('.detit-input, .detit-textarea');
            const fieldName = input.getAttribute('data-field');
            const btn = row.querySelector('.detit-add-single-btn');
            if (!btn.disabled || btn.textContent !== '✓') {
                fields[fieldName] = input.value;
            }
        });

        if (Object.keys(fields).length === 0) return;

        addAllBtn.disabled = true;
        addAllBtn.textContent = 'Processing...';

        const formData = new URLSearchParams();
        formData.append('action', 'detit_add_all_fields');
        formData.append('product_id', productId);
        formData.append('fields', JSON.stringify(fields));
        formData.append('nonce', detitData.nonce);

        fetch(detitData.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                addAllBtn.textContent = '✓ Added All';
                addAllBtn.classList.add('detit-success');
                
                // Update all individual buttons and main page fields
                document.querySelectorAll('.detit-add-single-btn').forEach(btn => {
                    const fieldName = btn.getAttribute('data-field');
                    if (fields.hasOwnProperty(fieldName)) {
                        btn.textContent = '✓';
                        btn.disabled = true;
                        btn.classList.add('detit-success');
                        updateMainPageField(fieldName, fields[fieldName]);
                    }
                });
            } else {
                addAllBtn.disabled = false;
                addAllBtn.textContent = 'Add All Fields';
                alert('Error: ' + (res.data?.message || 'Failed to add fields'));
            }
        })
        .catch(err => {
            addAllBtn.disabled = false;
            addAllBtn.textContent = 'Add All Fields';
            alert('AJAX error occurred');
        });
    });

    function updateMainPageField(fieldName, value) {
        if (fieldName === 'title') {
            const titleInput = document.getElementById('title');
            if (titleInput) {
                titleInput.value = value;
                // Dispatch event so WP block editor or other scripts know it changed
                titleInput.dispatchEvent(new Event('input', { bubbles: true }));
                titleInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            const titlePrompt = document.getElementById('title-prompt-text');
            if (titlePrompt) titlePrompt.classList.add('screen-reader-text');
        } else if (fieldName === 'description') {
            const contentInput = document.getElementById('content');
            if (contentInput) {
                contentInput.value = value;
            }
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get('content')) {
                tinyMCE.get('content').setContent(value);
            }
        } else if (fieldName === 'short_description') {
            const excerptInput = document.getElementById('excerpt');
            if (excerptInput) {
                excerptInput.value = value;
            }
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get('excerpt')) {
                tinyMCE.get('excerpt').setContent(value);
            }
        } else if (fieldName === 'tags') {
            const tagsInput = document.getElementById('new-tag-product_tag');
            if (tagsInput) {
                tagsInput.value = value;
                const addTagBtn = document.querySelector('#product_tag .tagadd');
                if (addTagBtn) {
                    addTagBtn.click();
                }
            }
        } else {
            // For custom meta fields (SEO etc.), try matching by name or id
            const metaInput = document.querySelector(`[name="${fieldName}"], [id="${fieldName}"]`);
            if (metaInput) {
                metaInput.value = value;
                metaInput.dispatchEvent(new Event('input', { bubbles: true }));
                metaInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    }
});
