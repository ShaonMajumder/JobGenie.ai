import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Autosave draft for the job session form to localStorage
document.addEventListener('DOMContentLoaded', function () {
	try {
		const form = document.getElementById('job-session-form');
		if (!form) return;

		const STORAGE_KEY = 'job_session_draft_v1';

		// Load draft if any and only populate empty fields (server defaults kept)
		const draft = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
		if (draft) {
			for (const [name, value] of Object.entries(draft)) {
				const el = form.querySelector(`[name=\"${name}\"]`);
				if (!el) continue;
				// Don't overwrite server-populated values
				if (el.type === 'checkbox' || el.type === 'radio') continue;
				if (!el.value) {
					el.value = value;
				}
			}
		}

		// Save inputs to localStorage on change
		const saveDraft = () => {
			const data = {};
			Array.from(form.elements).forEach((el) => {
				if (!el.name) return;
				if (el.tagName === 'BUTTON') return;
				if (el.type === 'checkbox') return;
				data[el.name] = el.value;
			});
			localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
		};

		form.addEventListener('input', saveDraft);
		form.addEventListener('change', saveDraft);

		// Clear draft on successful submit (let server handle validation/errors)
		form.addEventListener('submit', function () {
			localStorage.removeItem(STORAGE_KEY);
		});
	} catch (e) {
		// ignore — non-critical
		console.error('Job form autosave error', e);
	}
});
