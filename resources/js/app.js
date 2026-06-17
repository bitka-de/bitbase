import { EditorState } from '@codemirror/state';
import { EditorView, basicSetup } from 'codemirror';
import { html } from '@codemirror/lang-html';

const initCmsEditor = () => {
	const formRoot = document.querySelector('[data-cms-form]');
	if (!formRoot) {
		return;
	}

	const tabButtons = [...formRoot.querySelectorAll('[data-cms-tab-btn]')];
	const panes = [...formRoot.querySelectorAll('[data-cms-tab-pane]')];
	const source = formRoot.querySelector('#content-source');
	const codeHost = formRoot.querySelector('#content-code-editor');
	const wysiwygFrame = formRoot.querySelector('#content-wysiwyg');
	const previewFrame = formRoot.querySelector('#content-preview');
	const modeButtons = [...formRoot.querySelectorAll('[data-editor-mode]')];
	const viewportButtons = [...formRoot.querySelectorAll('[data-editor-viewport-btn]')];
	const fullscreenButton = formRoot.querySelector('[data-editor-fullscreen]');
	const editorSurface = formRoot.querySelector('[data-editor-surface]');
	const cmsForm = formRoot.closest('form');
	const saveButton = cmsForm?.querySelector('[data-save-button]');
	const titleInput = formRoot.querySelector('#title');
	const slugInput = formRoot.querySelector('#slug');
	const excerptInput = formRoot.querySelector('#excerpt');
	const seoTitleInput = formRoot.querySelector('#seo_title');
	const metaDescriptionInput = formRoot.querySelector('#meta_description');
	const canonicalUrlInput = formRoot.querySelector('#canonical_url');
	const templateSelect = formRoot.querySelector('#template');
	const serpPreview = formRoot.querySelector('[data-serp-preview]');
	const serpDeviceButtons = [...formRoot.querySelectorAll('[data-serp-device-btn]')];
	const serpSiteEl = formRoot.querySelector('[data-serp-site]');
	const serpTitleEl = formRoot.querySelector('[data-serp-title]');
	const serpUrlEl = formRoot.querySelector('[data-serp-url]');
	const serpDescEl = formRoot.querySelector('[data-serp-desc]');
	const pageTitleEl = formRoot.querySelector('[data-cms-page-title]');
	const editorWordsMetaEl = formRoot.querySelector('[data-editor-meta-words]');
	const editorCharsMetaEl = formRoot.querySelector('[data-editor-meta-chars]');
	const editorReadingMetaEl = formRoot.querySelector('[data-editor-meta-reading]');
	const frontendCssHref = formRoot.dataset.frontendCss || '';
	const appName = formRoot.dataset.appName || 'Website';
	const previewYear = formRoot.dataset.previewYear || '';
	const editorModeStorageKey = `cms-editor-mode:${window.location.pathname}`;
	const editorViewportStorageKey = `cms-editor-viewport:${window.location.pathname}`;

	if (
		!(source instanceof HTMLTextAreaElement) ||
		!(codeHost instanceof HTMLElement) ||
		!(wysiwygFrame instanceof HTMLIFrameElement) ||
		!(previewFrame instanceof HTMLIFrameElement)
	) {
		return;
	}

	const activateTab = (name) => {
		tabButtons.forEach((button) => {
			const active = button.dataset.cmsTabBtn === name;
			button.classList.toggle('is-active', active);
			button.setAttribute('aria-selected', active ? 'true' : 'false');
		});

		panes.forEach((pane) => {
			pane.hidden = pane.dataset.cmsTabPane !== name;
		});
	};

	tabButtons.forEach((button) => {
		button.addEventListener('click', () => activateTab(button.dataset.cmsTabBtn));
	});

	activateTab('content');

	const slugify = (value) => {
		return value
			.toString()
			.normalize('NFD')
			.replace(/[\u0300-\u036f]/g, '')
			.toLowerCase()
			.replace(/[^a-z0-9\s-]/g, '')
			.trim()
			.replace(/\s+/g, '-')
			.replace(/-+/g, '-');
	};

	let serpDevice = serpPreview?.dataset.serpDevice || 'desktop';

	const truncateForSnippet = (value, maxLength) => {
		const text = (value || '').replace(/\s+/g, ' ').trim();
		if (text.length <= maxLength) {
			return text;
		}

		return `${text.slice(0, maxLength - 1).trimEnd()}…`;
	};

	const buildSerpUrl = () => {
		const canonicalValue = canonicalUrlInput instanceof HTMLInputElement ? canonicalUrlInput.value.trim() : '';
		if (canonicalValue) {
			return canonicalValue;
		}

		const slug = slugInput instanceof HTMLInputElement ? slugInput.value.trim() : '';
		const path = slug ? `/${slug.replace(/^\/+/, '')}` : '/seite';
		return `${window.location.origin}${path}`;
	};

	const normalizeDisplayUrl = (rawUrl) => {
		try {
			const parsed = new URL(rawUrl, window.location.origin);
			const host = parsed.host.replace(/^www\./, '');
			const pathParts = parsed.pathname
				.split('/')
				.filter(Boolean)
				.map((part) => decodeURIComponent(part));
			const path = pathParts.length ? ` › ${pathParts.join(' › ')}` : '';
			return `${host}${path}`;
		} catch {
			return rawUrl;
		}
	};

	const normalizeTemplate = (template) => {
		return ['default', 'focused', 'story'].includes(template) ? template : 'default';
	};

	const slashCommands = [
		{
			id: 'heading-1',
			label: 'Heading 1 (H1)',
			keywords: ['h1', 'heading', 'titel', 'headline'],
			create: (doc) => {
				const node = doc.createElement('h1');
				node.textContent = 'Hauptueberschrift';
				return node;
			},
		},
		{
			id: 'heading-2',
			label: 'Heading 2 (H2)',
			keywords: ['h2', 'heading', 'untertitel', 'subheadline'],
			create: (doc) => {
				const node = doc.createElement('h2');
				node.textContent = 'Abschnittstitel';
				return node;
			},
		},
		{
			id: 'paragraph',
			label: 'Paragraph (P)',
			keywords: ['p', 'paragraph', 'text', 'absatz'],
			create: (doc) => {
				const node = doc.createElement('p');
				node.textContent = 'Neuer Absatztext';
				return node;
			},
		},
		{
			id: 'list-unordered',
			label: 'Liste (UL)',
			keywords: ['ul', 'liste', 'list', 'bullet'],
			create: (doc) => {
				const node = doc.createElement('ul');
				const li = doc.createElement('li');
				li.textContent = 'Listenpunkt';
				node.appendChild(li);
				return node;
			},
		},
		{
			id: 'list-ordered',
			label: 'Nummerierte Liste (OL)',
			keywords: ['ol', 'nummeriert', 'liste', 'ordered'],
			create: (doc) => {
				const node = doc.createElement('ol');
				const li = doc.createElement('li');
				li.textContent = 'Listenpunkt';
				node.appendChild(li);
				return node;
			},
		},
		{
			id: 'section-faq',
			label: 'Sektion FAQ',
			keywords: ['faq', 'section', 'bereich', 'fragen', 'accordion'],
			create: (doc) => {
				const section = doc.createElement('section');
				section.className = 'cms-content-section cms-content-section-faq';

				const heading = doc.createElement('h2');
				heading.textContent = 'Haeufige Fragen';
				section.appendChild(heading);

				const intro = doc.createElement('p');
				intro.textContent = 'Hier findest du die wichtigsten Antworten kurz und klar zusammengefasst.';
				section.appendChild(intro);

				const list = doc.createElement('div');
				list.className = 'cms-faq-list';

				for (let i = 1; i <= 3; i += 1) {
					const item = doc.createElement('details');
					item.className = 'cms-faq-item';

					const summary = doc.createElement('summary');
					summary.textContent = `Frage ${i}`;

					const answer = doc.createElement('p');
					answer.textContent = 'Antworttext fuer diese Frage. Hier kannst du konkrete Details eintragen.';

					item.append(summary, answer);
					list.appendChild(item);
				}

				section.appendChild(list);
				return section;
			},
		},
		{
			id: 'section-two-columns',
			label: 'Sektion 2 Spalten',
			keywords: ['section', 'bereich', '2 spalten', 'columns', 'layout'],
			create: (doc) => {
				const section = doc.createElement('section');
				section.className = 'cms-content-section cms-content-section-columns';

				const heading = doc.createElement('h2');
				heading.textContent = 'Abschnitt mit zwei Spalten';
				section.appendChild(heading);

				const grid = doc.createElement('div');
				grid.className = 'cms-columns';

				const left = doc.createElement('div');
				left.className = 'cms-column';
				left.innerHTML = '<h3>Spalte 1</h3><p>Inhalt fuer die erste Spalte.</p>';

				const right = doc.createElement('div');
				right.className = 'cms-column';
				right.innerHTML = '<h3>Spalte 2</h3><p>Inhalt fuer die zweite Spalte.</p>';

				grid.append(left, right);
				section.appendChild(grid);
				return section;
			},
		},
		{
			id: 'section-cta',
			label: 'Sektion CTA',
			keywords: ['section', 'bereich', 'cta', 'call to action', 'button'],
			create: (doc) => {
				const section = doc.createElement('section');
				section.className = 'cms-content-section cms-content-section-cta';

				const heading = doc.createElement('h2');
				heading.textContent = 'Bereit fuer den naechsten Schritt?';

				const text = doc.createElement('p');
				text.textContent = 'Kurzer CTA Text mit Nutzen und klarer Handlungsaufforderung.';

				const buttonWrap = doc.createElement('p');
				const link = doc.createElement('a');
				link.href = '#';
				link.className = 'btn btn-primary';
				link.textContent = 'Jetzt starten';
				buttonWrap.appendChild(link);

				section.append(heading, text, buttonWrap);
				return section;
			},
		},
		{
			id: 'quote',
			label: 'Zitat',
			keywords: ['zitat', 'quote', 'blockquote'],
			create: (doc) => {
				const node = doc.createElement('blockquote');
				node.textContent = 'Zitattext';
				return node;
			},
		},
		{
			id: 'button',
			label: 'Button Link',
			keywords: ['button', 'cta', 'link'],
			create: (doc) => {
				const wrap = doc.createElement('p');
				const link = doc.createElement('a');
				link.href = '#';
				link.className = 'btn btn-primary';
				link.textContent = 'Jetzt starten';
				wrap.appendChild(link);
				return wrap;
			},
		},
	];

	const setSerpDevice = (device) => {
		if (!(serpPreview instanceof HTMLElement)) {
			return;
		}

		serpDevice = device === 'mobile' ? 'mobile' : 'desktop';
		serpPreview.dataset.serpDevice = serpDevice;

		serpDeviceButtons.forEach((button) => {
			const active = button.dataset.serpDeviceBtn === serpDevice;
			button.classList.toggle('is-active', active);
			button.setAttribute('aria-selected', active ? 'true' : 'false');
		});
	};

	const syncSerpPreview = () => {
		if (!(serpTitleEl instanceof HTMLElement) || !(serpUrlEl instanceof HTMLElement) || !(serpDescEl instanceof HTMLElement) || !(serpSiteEl instanceof HTMLElement)) {
			return;
		}

		const rawTitle = seoTitleInput instanceof HTMLInputElement
			? (seoTitleInput.value.trim() || (titleInput instanceof HTMLInputElement ? titleInput.value.trim() : ''))
			: (titleInput instanceof HTMLInputElement ? titleInput.value.trim() : '');
		const rawDescription = metaDescriptionInput instanceof HTMLTextAreaElement
			? (metaDescriptionInput.value.trim() || (excerptInput instanceof HTMLTextAreaElement ? excerptInput.value.trim() : ''))
			: (excerptInput instanceof HTMLTextAreaElement ? excerptInput.value.trim() : '');
		const snippetUrl = buildSerpUrl();

		const titleMaxLength = serpDevice === 'mobile' ? 68 : 60;
		const descriptionMaxLength = serpDevice === 'mobile' ? 120 : 155;

		serpTitleEl.textContent = truncateForSnippet(rawTitle || 'Seitentitel', titleMaxLength);
		serpUrlEl.textContent = normalizeDisplayUrl(snippetUrl);
		serpDescEl.textContent = truncateForSnippet(rawDescription || 'Meta Description Vorschau', descriptionMaxLength);

		try {
			const parsed = new URL(snippetUrl, window.location.origin);
			serpSiteEl.textContent = parsed.host.replace(/^www\./, '');
		} catch {
			serpSiteEl.textContent = window.location.host.replace(/^www\./, '');
		}
	};

	const formatHtml = (rawHtml) => {
		const sourceHtml = (rawHtml || '').replace(/\r\n/g, '\n').trim();
		if (!sourceHtml) {
			return '';
		}

		const voidTags = new Set(['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr']);
		const preserveTags = new Set(['pre', 'code', 'textarea', 'script', 'style']);
		const container = document.createElement('div');
		container.innerHTML = sourceHtml;
		const indent = '  ';

		const escapeText = (value) => {
			return value
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;');
		};

		const serializeNode = (node, level) => {
			if (node.nodeType === Node.TEXT_NODE) {
				const compactText = (node.textContent || '').replace(/\s+/g, ' ').trim();
				if (!compactText) {
					return '';
				}

				return `${indent.repeat(level)}${escapeText(compactText)}`;
			}

			if (node.nodeType === Node.COMMENT_NODE) {
				return `${indent.repeat(level)}<!--${node.textContent || ''}-->`;
			}

			if (node.nodeType !== Node.ELEMENT_NODE) {
				return '';
			}

			const element = node;
			const tag = element.tagName.toLowerCase();
			const attrs = [...element.attributes]
				.map((attr) => ` ${attr.name}="${attr.value.replace(/"/g, '&quot;')}"`)
				.join('');
			const pad = indent.repeat(level);

			if (voidTags.has(tag)) {
				return `${pad}<${tag}${attrs}>`;
			}

			if (preserveTags.has(tag)) {
				const rawInner = element.innerHTML;
				return `${pad}<${tag}${attrs}>${rawInner}</${tag}>`;
			}

			const serializedChildren = [...element.childNodes]
				.map((child) => serializeNode(child, level + 1))
				.filter(Boolean);

			if (!serializedChildren.length) {
				return `${pad}<${tag}${attrs}></${tag}>`;
			}

			if (serializedChildren.length === 1 && !serializedChildren[0].includes('\n')) {
				const inline = serializedChildren[0].trim();
				return `${pad}<${tag}${attrs}>${inline}</${tag}>`;
			}

			return `${pad}<${tag}${attrs}>\n${serializedChildren.join('\n')}\n${pad}</${tag}>`;
		};

		return [...container.childNodes]
			.map((child) => serializeNode(child, 0))
			.filter(Boolean)
			.join('\n\n');
	};

	const createFrontendFrameDocument = (frame, mode) => {
		const doc = frame.contentDocument;
		if (!doc) {
			return null;
		}

		const bodyContent = mode === 'preview'
			? `<div class="site-shell cms-frame-shell">
				<header class="layout-header cms-frame-header">
					<div class="surface layout-bar boxed">
						<a href="#" class="brand-mark" aria-label="${appName} Startseite">
							<span class="brand-dot" aria-hidden="true"></span>
							<span>${appName}</span>
						</a>
						<nav class="layout-nav" aria-label="Hauptnavigation">
							<a href="#" class="soft-link">Home</a>
							<a href="#" class="soft-link">Stylebook</a>
							<a href="#" class="soft-link">Leistungen</a>
							<a href="#" class="soft-link">Kontakt</a>
						</nav>
					</div>
				</header>
				<main class="layout-main cms-frame-main" id="main-content">
					<section class="surface home-main boxed cms-frame-article">
						<header class="home-hero cms-frame-hero">
							<span class="accent-badge">Vorschau</span>
							<h1 class="home-title cms-frame-title"></h1>
							<p class="home-lead cms-frame-excerpt"></p>
						</header>
						<section class="cms-frame-editable"></section>
					</section>
				</main>
				<footer class="layout-footer cms-frame-footer">
					<div class="surface layout-footer-inner boxed">
						<span>${previewYear} ${appName}</span>
						<span aria-hidden="true">·</span>
						<a href="#" class="soft-link">Impressum</a>
						<a href="#" class="soft-link">Datenschutz</a>
					</div>
				</footer>
			</div>`
			: `<div class="site-shell cms-frame-shell">
				<main class="layout-main cms-frame-main">
					<div class="container-page cms-frame-page">
						<article class="surface cms-frame-surface">
							<div class="cms-frame-editable" contenteditable="true"></div>
						</article>
					</div>
				</main>
			</div>`;

		doc.open();
		doc.write(`<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="${frontendCssHref}">
    <style>
		body { margin: 0; background: #f6f8fc; color: #0f172a; }
		.cms-frame-shell { min-height: 100vh; }
		.cms-frame-main { padding: 1rem; }
		.cms-frame-page { width: min(100%, var(--cms-frame-width, 1080px)); margin: 0 auto; transition: width 180ms ease, max-width 180ms ease; }
		.cms-frame-surface { background: var(--cms-frame-surface-bg, rgba(255, 255, 255, 0.88)); border-radius: 1rem; padding: var(--cms-frame-surface-padding, 1rem); min-height: 220px; border: 1px solid var(--cms-frame-surface-border, rgba(17, 24, 39, 0.08)); box-shadow: var(--cms-frame-surface-shadow, 0 14px 30px rgba(15, 23, 42, 0.08)); }
        .cms-frame-editable:focus { outline: none; }
        .cms-frame-header[hidden], .cms-frame-footer[hidden] { display: none !important; }
        .cms-frame-excerpt:empty { display: none; }

		body[data-page-template='default'] {
			--cms-frame-width: 1080px;
			--cms-frame-surface-bg: rgba(255, 255, 255, 0.88);
			--cms-frame-surface-padding: 1rem;
			--cms-frame-surface-border: rgba(17, 24, 39, 0.08);
			--cms-frame-surface-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
		}

		body[data-page-template='focused'] {
			--cms-frame-width: 820px;
			--cms-frame-surface-bg: rgba(255, 255, 255, 0.96);
			--cms-frame-surface-padding: 1.2rem;
			--cms-frame-surface-border: rgba(17, 24, 39, 0.09);
			--cms-frame-surface-shadow: 0 18px 38px rgba(15, 23, 42, 0.1);
		}

		body[data-page-template='story'] {
			--cms-frame-width: 960px;
			--cms-frame-surface-bg: linear-gradient(145deg, rgba(255, 255, 255, 0.98), rgba(242, 247, 255, 0.92));
			--cms-frame-surface-padding: 1.15rem;
			--cms-frame-surface-border: rgba(67, 90, 120, 0.12);
			--cms-frame-surface-shadow: 0 20px 42px rgba(15, 23, 42, 0.12);
		}

		body[data-page-template='focused'] .cms-frame-hero {
			margin-bottom: 0.8rem;
		}

		body[data-page-template='story'] .cms-frame-hero {
			padding-bottom: 0.6rem;
			border-bottom: 1px solid rgba(67, 90, 120, 0.12);
			margin-bottom: 0.9rem;
		}

		.cms-slash-menu {
			position: absolute;
			z-index: 1200;
			min-width: 220px;
			max-width: 300px;
			padding: 0.3rem;
			border-radius: 0.7rem;
			border: 1px solid rgba(18, 24, 38, 0.18);
			background: rgba(255, 255, 255, 0.98);
			box-shadow: 0 20px 42px rgba(9, 15, 30, 0.2);
			backdrop-filter: blur(5px);
		}

		.cms-slash-item {
			display: block;
			width: 100%;
			text-align: left;
			font: inherit;
			font-size: 0.86rem;
			font-weight: 600;
			color: #12203a;
			background: transparent;
			border: 0;
			border-radius: 0.5rem;
			padding: 0.45rem 0.55rem;
			cursor: pointer;
		}

		.cms-slash-item:hover,
		.cms-slash-item.is-active {
			background: rgba(41, 89, 197, 0.13);
		}

		.cms-slash-empty {
			padding: 0.45rem 0.55rem;
			font-size: 0.82rem;
			color: #5e6b82;
		}
    </style>
</head>
<body>${bodyContent}</body>
</html>`);
		doc.close();

		return {
			doc,
			editableNode: doc.querySelector('.cms-frame-editable'),
			titleNode: doc.querySelector('.cms-frame-title'),
			excerptNode: doc.querySelector('.cms-frame-excerpt'),
			headerNode: doc.querySelector('.cms-frame-header'),
			footerNode: doc.querySelector('.cms-frame-footer'),
		};
	};

	const wysiwygDocument = createFrontendFrameDocument(wysiwygFrame, 'wysiwyg');
	const previewDocument = createFrontendFrameDocument(previewFrame, 'preview');

	if (!wysiwygDocument?.editableNode || !previewDocument?.editableNode) {
		return;
	}

	const wysiwygEditable = wysiwygDocument.editableNode;
	const previewRoot = previewDocument.editableNode;
	let slugManuallyEdited = false;
	let suppressWysiwygRefresh = false;
	let fullscreenTransitionTimer;
	let metricsFrame = null;
	let editorViewport = window.sessionStorage.getItem(editorViewportStorageKey) || 'desktop';
	let activeTemplate = normalizeTemplate(templateSelect instanceof HTMLSelectElement ? templateSelect.value : 'default');
	let initialFormState = '';
	const slashState = {
		open: false,
		selectedIndex: 0,
		query: '',
		triggerRange: null,
		items: slashCommands,
	};

	const slashMenu = wysiwygDocument.doc.createElement('div');
	slashMenu.className = 'cms-slash-menu';
	slashMenu.hidden = true;
	wysiwygDocument.doc.body.appendChild(slashMenu);

	const ensureParagraphMode = () => {
		try {
			wysiwygDocument.doc.execCommand('defaultParagraphSeparator', false, 'p');
		} catch {
			// Browser may not support this command; Enter handler below is the fallback.
		}
	};

	const setEditorViewport = (viewport) => {
		if (!(editorSurface instanceof HTMLElement)) {
			return;
		}

		editorViewport = viewport === 'mobile' ? 'mobile' : 'desktop';
		editorSurface.dataset.editorViewport = editorViewport;
		window.sessionStorage.setItem(editorViewportStorageKey, editorViewport);

		viewportButtons.forEach((button) => {
			const active = button.dataset.editorViewportBtn === editorViewport;
			button.classList.toggle('is-active', active);
			button.setAttribute('aria-selected', active ? 'true' : 'false');
		});
	};

	const applyTemplateToFrames = (template) => {
		const nextTemplate = normalizeTemplate(template);

		if (editorSurface instanceof HTMLElement) {
			editorSurface.dataset.editorLayout = nextTemplate;
		}

		if (wysiwygDocument.doc?.body) {
			wysiwygDocument.doc.body.dataset.pageTemplate = nextTemplate;
		}

		if (previewDocument.doc?.body) {
			previewDocument.doc.body.dataset.pageTemplate = nextTemplate;
		}

		activeTemplate = nextTemplate;
	};

	const syncPreviewShellMeta = () => {
		const currentTitle = titleInput instanceof HTMLInputElement ? titleInput.value.trim() : '';
		const currentExcerpt = excerptInput instanceof HTMLTextAreaElement ? excerptInput.value.trim() : '';

		if (previewDocument.titleNode) {
			previewDocument.titleNode.textContent = currentTitle || 'Unbenannte Seite';
		}

		if (previewDocument.excerptNode) {
			previewDocument.excerptNode.textContent = currentExcerpt;
		}
	};

	const closeSlashMenu = () => {
		slashState.open = false;
		slashState.query = '';
		slashState.triggerRange = null;
		slashState.items = slashCommands;
		slashMenu.hidden = true;
		slashMenu.innerHTML = '';
	};

	const positionSlashMenu = () => {
		if (!slashState.open) {
			return;
		}

		const selection = wysiwygDocument.doc.getSelection();
		if (!selection || selection.rangeCount === 0) {
			return;
		}

		const range = selection.getRangeAt(0).cloneRange();
		range.collapse(true);
		const rect = range.getBoundingClientRect();
		const scrollTop = wysiwygDocument.doc.defaultView?.scrollY || 0;
		const scrollLeft = wysiwygDocument.doc.defaultView?.scrollX || 0;

		slashMenu.style.top = `${rect.bottom + scrollTop + 8}px`;
		slashMenu.style.left = `${rect.left + scrollLeft}px`;
	};

	const renderSlashMenu = () => {
		if (!slashState.open) {
			return;
		}

		if (!slashState.items.length) {
			slashMenu.innerHTML = '<div class="cms-slash-empty">Kein Treffer</div>';
			slashMenu.hidden = false;
			positionSlashMenu();
			return;
		}

		slashMenu.innerHTML = slashState.items
			.map((item, index) => {
				const activeClass = index === slashState.selectedIndex ? ' is-active' : '';
				return `<button type="button" class="cms-slash-item${activeClass}" data-slash-index="${index}">${item.label}</button>`;
			})
			.join('');

		slashMenu.hidden = false;
		positionSlashMenu();
	};

	const applySlashCommand = (item) => {
		if (!item || !slashState.triggerRange) {
			closeSlashMenu();
			return;
		}

		const selection = wysiwygDocument.doc.getSelection();
		if (!selection || selection.rangeCount === 0) {
			closeSlashMenu();
			return;
		}

		const currentRange = selection.getRangeAt(0);
		const replaceRange = wysiwygDocument.doc.createRange();
		replaceRange.setStart(slashState.triggerRange.startContainer, slashState.triggerRange.startOffset);
		replaceRange.setEnd(currentRange.endContainer, currentRange.endOffset);
		replaceRange.deleteContents();

		const node = item.create(wysiwygDocument.doc);
		replaceRange.insertNode(node);

		const caret = wysiwygDocument.doc.createRange();
		caret.setStartAfter(node);
		caret.collapse(true);
		selection.removeAllRanges();
		selection.addRange(caret);

		syncFromWysiwyg();
		closeSlashMenu();
	};

	const updateSlashFromSelection = () => {
		if (!slashState.open || !slashState.triggerRange) {
			return;
		}

		const selection = wysiwygDocument.doc.getSelection();
		if (!selection || selection.rangeCount === 0 || !selection.isCollapsed) {
			closeSlashMenu();
			return;
		}

		const range = selection.getRangeAt(0);
		const queryRange = wysiwygDocument.doc.createRange();
		queryRange.setStart(slashState.triggerRange.startContainer, slashState.triggerRange.startOffset);
		queryRange.setEnd(range.endContainer, range.endOffset);
		const queryText = queryRange.toString();

		if (!queryText.startsWith('/') || /\s/.test(queryText.slice(1))) {
			closeSlashMenu();
			return;
		}

		slashState.query = queryText.slice(1).toLowerCase();
		slashState.items = slashCommands.filter((item) => {
			const haystack = `${item.label} ${item.keywords.join(' ')}`.toLowerCase();
			return haystack.includes(slashState.query);
		});
		slashState.selectedIndex = 0;
		renderSlashMenu();
	};

	const openSlashMenu = (triggerRange) => {
		slashState.open = true;
		slashState.triggerRange = triggerRange;
		slashState.items = slashCommands;
		slashState.selectedIndex = 0;
		slashState.query = '';
		renderSlashMenu();
	};

	const serializeFormState = () => {
		if (!(cmsForm instanceof HTMLFormElement)) {
			return '';
		}

		const formData = new FormData(cmsForm);
		return JSON.stringify(Array.from(formData.entries()));
	};

	const updateSaveButtonState = () => {
		if (!(saveButton instanceof HTMLButtonElement)) {
			return;
		}

		const hasChanges = serializeFormState() !== initialFormState;
		saveButton.disabled = !hasChanges;
		saveButton.classList.toggle('is-disabled', !hasChanges);
	};

	const metricsBuffer = document.createElement('div');
	const extractEditorText = (rawHtml) => {
		metricsBuffer.innerHTML = rawHtml || '';
		return (metricsBuffer.textContent || '')
			.replace(/\s+/g, ' ')
			.trim();
	};

	const updateEditorMetaNow = () => {
		if (!(editorWordsMetaEl instanceof HTMLElement) || !(editorCharsMetaEl instanceof HTMLElement) || !(editorReadingMetaEl instanceof HTMLElement)) {
			return;
		}

		const text = extractEditorText(source.value);
		const words = text ? text.split(' ').length : 0;
		const chars = text.length;
		const readingMinutes = Math.max(1, Math.ceil(words / 220));

		editorWordsMetaEl.textContent = `${words} Woerter`;
		editorCharsMetaEl.textContent = `${chars} Zeichen`;
		editorReadingMetaEl.textContent = `~${readingMinutes} min Lesezeit`;
	};

	const queueEditorMetaUpdate = () => {
		if (metricsFrame !== null) {
			return;
		}

		metricsFrame = window.requestAnimationFrame(() => {
			metricsFrame = null;
			updateEditorMetaNow();
		});
	};

	const view = new EditorView({
		state: EditorState.create({
			doc: source.value || '',
			extensions: [
				basicSetup,
				html(),
				EditorView.updateListener.of((update) => {
					if (!update.docChanged) {
						return;
					}

					const value = update.state.doc.toString();
					source.value = value;

					if (!suppressWysiwygRefresh) {
						wysiwygEditable.innerHTML = value;
					}

					previewRoot.innerHTML = value;
					syncPreviewShellMeta();
					updateSaveButtonState();
					queueEditorMetaUpdate();
				}),
			],
		}),
		parent: codeHost,
	});

	const syncFromSource = () => {
		const value = source.value;
		const current = view.state.doc.toString();

		if (value !== current) {
			view.dispatch({ changes: { from: 0, to: current.length, insert: value } });
		}

		wysiwygEditable.innerHTML = value;
		previewRoot.innerHTML = value;
		syncPreviewShellMeta();
		updateSaveButtonState();
		queueEditorMetaUpdate();
	};

	const syncFromWysiwyg = () => {
		const value = wysiwygEditable.innerHTML;
		source.value = value;

		const current = view.state.doc.toString();
		if (value !== current) {
			suppressWysiwygRefresh = true;
			view.dispatch({ changes: { from: 0, to: current.length, insert: value } });
			suppressWysiwygRefresh = false;
		}

		previewRoot.innerHTML = value;
		syncPreviewShellMeta();
		updateSaveButtonState();
		queueEditorMetaUpdate();
	};

	const setMode = (mode) => {
		const nextMode = ['html', 'wysiwyg', 'preview'].includes(mode) ? mode : 'wysiwyg';

		modeButtons.forEach((btn) => btn.classList.toggle('is-active', btn.dataset.editorMode === nextMode));
		codeHost.hidden = nextMode !== 'html';
		wysiwygFrame.hidden = nextMode !== 'wysiwyg';
		previewFrame.hidden = nextMode !== 'preview';
		formRoot.dataset.editorMode = nextMode;
		window.sessionStorage.setItem(editorModeStorageKey, nextMode);
	};

	const setFullscreen = (active) => {
		if (!(editorSurface instanceof HTMLElement) || !(fullscreenButton instanceof HTMLElement)) {
			return;
		}

		if (fullscreenTransitionTimer) {
			window.clearTimeout(fullscreenTransitionTimer);
		}

		const updateFullscreenButton = (isActive) => {
			fullscreenButton.dataset.editorFullscreen = isActive ? 'true' : 'false';
			fullscreenButton.classList.toggle('is-active', isActive);
			fullscreenButton.setAttribute('aria-label', isActive ? 'Vollbild beenden' : 'Editor im Vollbild anzeigen');
			fullscreenButton.setAttribute('title', isActive ? 'Vollbild beenden' : 'Vollbild');
		};

		if (active) {
			editorSurface.classList.remove('is-closing');
			editorSurface.classList.add('is-fullscreen', 'is-opening');
			document.body.classList.add('cms-editor-fullscreen');
			updateFullscreenButton(true);

			fullscreenTransitionTimer = window.setTimeout(() => {
				editorSurface.classList.remove('is-opening');
			}, 220);
			return;
		}

		if (!editorSurface.classList.contains('is-fullscreen')) {
			document.body.classList.remove('cms-editor-fullscreen');
			updateFullscreenButton(false);
			return;
		}

		editorSurface.classList.remove('is-opening');
		editorSurface.classList.add('is-closing');
		updateFullscreenButton(false);

		fullscreenTransitionTimer = window.setTimeout(() => {
			editorSurface.classList.remove('is-closing');
			window.requestAnimationFrame(() => {
				editorSurface.classList.remove('is-fullscreen');
				document.body.classList.remove('cms-editor-fullscreen');
			});
		}, 180);
	};

	if (slugInput instanceof HTMLInputElement) {
		slugInput.addEventListener('input', () => {
			slugManuallyEdited = slugInput.value.trim() !== '';
			syncSerpPreview();
		});
	}

	if (titleInput instanceof HTMLInputElement) {
		titleInput.addEventListener('input', () => {
			const titleValue = titleInput.value.trim();

			if (pageTitleEl instanceof HTMLElement) {
				pageTitleEl.textContent = titleValue || 'Neue Seite';
			}

			if (slugInput instanceof HTMLInputElement && !slugManuallyEdited) {
				slugInput.value = slugify(titleValue);
			}

			syncPreviewShellMeta();
			syncSerpPreview();
		});
	}

	if (excerptInput instanceof HTMLTextAreaElement) {
		excerptInput.addEventListener('input', () => {
			syncPreviewShellMeta();
			syncSerpPreview();
		});
	}

	if (seoTitleInput instanceof HTMLInputElement) {
		seoTitleInput.addEventListener('input', syncSerpPreview);
	}

	if (metaDescriptionInput instanceof HTMLTextAreaElement) {
		metaDescriptionInput.addEventListener('input', syncSerpPreview);
	}

	if (canonicalUrlInput instanceof HTMLInputElement) {
		canonicalUrlInput.addEventListener('input', syncSerpPreview);
	}

	if (templateSelect instanceof HTMLSelectElement) {
		templateSelect.addEventListener('change', () => {
			applyTemplateToFrames(templateSelect.value);
			updateSaveButtonState();
		});
	}

	serpDeviceButtons.forEach((button) => {
		button.addEventListener('click', () => {
			setSerpDevice(button.dataset.serpDeviceBtn || 'desktop');
			syncSerpPreview();
		});
	});

	source.addEventListener('input', syncFromSource);
	wysiwygEditable.addEventListener('input', syncFromWysiwyg);
	wysiwygEditable.addEventListener('input', updateSlashFromSelection);
	wysiwygEditable.addEventListener('focus', ensureParagraphMode);
	wysiwygEditable.addEventListener('keydown', (event) => {
		if (event.key !== 'Enter' || event.shiftKey || event.metaKey || event.ctrlKey || event.altKey) {
			return;
		}

		if (slashState.open) {
			return;
		}

		const selection = wysiwygDocument.doc.getSelection();
		if (!selection || selection.rangeCount === 0) {
			return;
		}

		const anchorElement = selection.anchorNode instanceof Element
			? selection.anchorNode
			: selection.anchorNode?.parentElement;

		if (anchorElement?.closest('li')) {
			return;
		}

		event.preventDefault();
		ensureParagraphMode();
		wysiwygDocument.doc.execCommand('insertParagraph');
	});
	modeButtons.forEach((btn) => btn.addEventListener('click', () => setMode(btn.dataset.editorMode || 'html')));
	viewportButtons.forEach((button) => {
		button.addEventListener('click', () => {
			setEditorViewport(button.dataset.editorViewportBtn || 'desktop');
		});
	});

	if (fullscreenButton instanceof HTMLElement) {
		fullscreenButton.addEventListener('click', () => {
			const active = fullscreenButton.dataset.editorFullscreen === 'true';
			setFullscreen(!active);
		});
	}

	if (cmsForm instanceof HTMLFormElement) {
		cmsForm.addEventListener('input', updateSaveButtonState);
		cmsForm.addEventListener('change', updateSaveButtonState);
	}

	wysiwygDocument.doc.addEventListener('keydown', (event) => {
		if (!slashState.open && event.key === '/' && !event.metaKey && !event.ctrlKey && !event.altKey) {
			const selection = wysiwygDocument.doc.getSelection();
			if (selection && selection.rangeCount > 0 && selection.isCollapsed) {
				const triggerRange = selection.getRangeAt(0).cloneRange();
				window.setTimeout(() => openSlashMenu(triggerRange), 0);
			}
			return;
		}

		if (!slashState.open) {
			return;
		}

		if (event.key === 'Escape') {
			event.preventDefault();
			closeSlashMenu();
			return;
		}

		if (event.key === 'ArrowDown') {
			event.preventDefault();
			if (!slashState.items.length) {
				return;
			}

			slashState.selectedIndex = (slashState.selectedIndex + 1) % slashState.items.length;
			renderSlashMenu();
			return;
		}

		if (event.key === 'ArrowUp') {
			event.preventDefault();
			if (!slashState.items.length) {
				return;
			}

			slashState.selectedIndex = (slashState.selectedIndex - 1 + slashState.items.length) % slashState.items.length;
			renderSlashMenu();
			return;
		}

		if (event.key === 'Enter' || event.key === 'Tab') {
			event.preventDefault();
			applySlashCommand(slashState.items[slashState.selectedIndex]);
			return;
		}

		if (event.key === ' ') {
			closeSlashMenu();
		}
	});

	wysiwygDocument.doc.addEventListener('mousedown', (event) => {
		const target = event.target;
		if (!(target instanceof HTMLElement)) {
			return;
		}

		const itemEl = target.closest('[data-slash-index]');
		if (!itemEl) {
			closeSlashMenu();
			return;
		}

		event.preventDefault();
		const index = Number(itemEl.getAttribute('data-slash-index'));
		applySlashCommand(slashState.items[index]);
	});

	wysiwygDocument.doc.addEventListener('beforeinput', (event) => {
		if (slashState.open || event.inputType !== 'insertText' || event.data !== '/') {
			return;
		}

		const selection = wysiwygDocument.doc.getSelection();
		if (!selection || selection.rangeCount === 0 || !selection.isCollapsed) {
			return;
		}

		const triggerRange = selection.getRangeAt(0).cloneRange();
		window.setTimeout(() => openSlashMenu(triggerRange), 0);
	});

	wysiwygDocument.doc.addEventListener('scroll', positionSlashMenu, true);

	document.addEventListener('keydown', (event) => {
		const isSaveShortcut = (event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 's';
		if (isSaveShortcut && formRoot.dataset.editorMode === 'html') {
			event.preventDefault();

			const current = view.state.doc.toString();
			const formatted = formatHtml(current);
			const nextValue = formatted || current;

			if (nextValue !== current) {
				view.dispatch({ changes: { from: 0, to: current.length, insert: nextValue } });
			}

			source.value = nextValue;

			if (cmsForm instanceof HTMLFormElement) {
				cmsForm.requestSubmit();
			}
			return;
		}

		if (event.key === 'Escape') {
			setFullscreen(false);
		}
	});

	cmsForm?.addEventListener('submit', () => {
		const activeMode = formRoot.dataset.editorMode || 'wysiwyg';
		window.sessionStorage.setItem(editorModeStorageKey, activeMode);

		if (formRoot.dataset.editorMode === 'wysiwyg') {
			syncFromWysiwyg();
		}

		const schemaText = formRoot.querySelector('#schema_data_text')?.value?.trim();
		const redirectsText = formRoot.querySelector('#redirect_old_urls_text')?.value?.trim();

		const schemaInput = formRoot.querySelector('#schema_data');
		const redirectsInput = formRoot.querySelector('#redirect_old_urls');

		if (schemaInput instanceof HTMLInputElement) {
			schemaInput.value = schemaText ? schemaText : '';
		}

		if (redirectsInput instanceof HTMLInputElement) {
			redirectsInput.value = redirectsText ? redirectsText : '';
		}

		if (saveButton instanceof HTMLButtonElement) {
			saveButton.disabled = true;
			saveButton.classList.add('is-disabled');
		}
	});

	syncFromSource();
	syncPreviewShellMeta();
	setSerpDevice(serpDevice);
	syncSerpPreview();
	ensureParagraphMode();
	applyTemplateToFrames(activeTemplate);
	setEditorViewport(editorViewport);
	updateEditorMetaNow();
	initialFormState = serializeFormState();
	updateSaveButtonState();

	const storedMode = window.sessionStorage.getItem(editorModeStorageKey);
	setMode(storedMode || 'wysiwyg');
};

document.addEventListener('DOMContentLoaded', initCmsEditor);

const submitRevisionRestore = (url, revisionId, csrfToken) => {
	const form = document.createElement('form');
	form.method = 'POST';
	form.action = url;

	const csrfInput = document.createElement('input');
	csrfInput.type = 'hidden';
	csrfInput.name = '_token';
	csrfInput.value = csrfToken;

	const revInput = document.createElement('input');
	revInput.type = 'hidden';
	revInput.name = 'revision_id';
	revInput.value = revisionId;

	form.append(csrfInput, revInput);
	document.body.appendChild(form);
	form.submit();
};

const submitRevisionPrune = (url, csrfToken) => {
	const form = document.createElement('form');
	form.method = 'POST';
	form.action = url;

	const csrfInput = document.createElement('input');
	csrfInput.type = 'hidden';
	csrfInput.name = '_token';
	csrfInput.value = csrfToken;

	form.append(csrfInput);
	document.body.appendChild(form);
	form.submit();
};

const initRevisionPreview = () => {
	const cards = [...document.querySelectorAll('[data-revision-card]')];
	if (!cards.length) {
		return;
	}

	const emptyPanel = document.querySelector('[data-revision-preview-empty]');
	const activePanel = document.querySelector('[data-revision-preview-active]');
	const previewFrame = document.querySelector('[data-revision-preview-frame]');
	const previewLabel = document.querySelector('[data-revision-preview-label]');
	const previewLoading = document.querySelector('[data-revision-preview-loading]');
	const previewSpinner = document.querySelector('.cms-revisions-preview-spinner');
	const restoreConfirmBtn = document.querySelector('[data-revision-restore-confirm]');
	const pruneButton = document.querySelector('[data-revisions-prune]');
	const formRoot = document.querySelector('[data-cms-form]');
	const csrfToken = formRoot?.dataset?.csrf || '';
	const frontendCss = formRoot?.dataset?.frontendCss || '';
	const appName = formRoot?.dataset?.appName || 'Website';
	const previewYear = formRoot?.dataset?.previewYear || '';

	if (!emptyPanel || !activePanel || !(previewFrame instanceof HTMLIFrameElement) || !previewLabel || !previewLoading || !restoreConfirmBtn) {
		return;
	}

	if (pruneButton instanceof HTMLButtonElement) {
		pruneButton.addEventListener('click', () => {
			const pruneUrl = pruneButton.dataset.pruneUrl || '';
			if (!pruneUrl || !csrfToken) {
				return;
			}

			const ok = window.confirm('Alle alten Versionen loeschen und nur die aktuelle behalten?');
			if (!ok) {
				return;
			}

			submitRevisionPrune(pruneUrl, csrfToken);
		});
	}

	let activeRestoreUrl = '';
	let activeRestoreId = '';
	let activeRevisionIsCurrent = false;
	let previewLoadToken = 0;
	let spinnerAnimation = null;

	const getRevisionPayload = (card) => {
		const payloadNode = card.querySelector('[data-revision-payload]');
		if (!(payloadNode instanceof HTMLScriptElement)) {
			return null;
		}

		try {
			return JSON.parse(payloadNode.textContent || '{}');
		} catch {
			return null;
		}
	};

	const writeRevisionFrame = (content, title, excerpt, template) => {
		const doc = previewFrame.contentDocument;
		if (!doc) {
			return;
		}

		const safeTemplate = ['default', 'focused', 'story'].includes(template) ? template : 'default';

		doc.open();
		doc.write(`<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="${frontendCss}">
<style>
body { margin: 0; background: #f6f8fc; color: #0f172a; }
.cms-frame-shell { min-height: 100vh; }
.cms-frame-main { padding: 1rem; }
.cms-frame-header[hidden], .cms-frame-footer[hidden] { display: none !important; }
.cms-frame-excerpt:empty { display: none; }
.cms-frame-article { width: min(100%, var(--cms-frame-width, 1080px)); margin: 0 auto; }

body[data-page-template='default'] {
	--cms-frame-width: 1080px;
}

body[data-page-template='focused'] {
	--cms-frame-width: 820px;
}

body[data-page-template='story'] {
	--cms-frame-width: 960px;
}
</style>
</head>
<body data-page-template="${safeTemplate}">
<div class="site-shell cms-frame-shell">
	<header class="layout-header cms-frame-header">
		<div class="surface layout-bar boxed">
			<a href="#" class="brand-mark" aria-label="${appName} Startseite">
				<span class="brand-dot" aria-hidden="true"></span>
				<span>${appName}</span>
			</a>
			<nav class="layout-nav" aria-label="Hauptnavigation">
				<a href="#" class="soft-link">Home</a>
				<a href="#" class="soft-link">Leistungen</a>
				<a href="#" class="soft-link">Kontakt</a>
			</nav>
		</div>
	</header>
	<main class="layout-main cms-frame-main" id="main-content">
		<section class="surface home-main boxed cms-frame-article">
			<header class="home-hero cms-frame-hero">
				<span class="accent-badge">Version</span>
				<h1 class="home-title cms-frame-title">${title || 'Unbenannte Seite'}</h1>
				<p class="home-lead cms-frame-excerpt">${excerpt || ''}</p>
			</header>
			<section class="cms-frame-editable">${content || '<p style="color:#8a9ab5">Kein Inhalt in dieser Version.</p>'}</section>
		</section>
	</main>
	<footer class="layout-footer cms-frame-footer">
		<div class="surface layout-footer-inner boxed">
			<span>${previewYear} ${appName}</span>
			<span aria-hidden="true">·</span>
			<a href="#" class="soft-link">Impressum</a>
			<a href="#" class="soft-link">Datenschutz</a>
		</div>
	</footer>
</div>
</body>
</html>`);
		doc.close();
	};

	const setPreviewLoading = (loading) => {
		activePanel.classList.toggle('is-loading', loading);
		previewLoading.hidden = !loading;

		if (!(previewSpinner instanceof HTMLElement) || typeof previewSpinner.animate !== 'function') {
			return;
		}

		if (loading) {
			spinnerAnimation?.cancel();
			spinnerAnimation = previewSpinner.animate(
				[
					{ transform: 'rotate(0deg)' },
					{ transform: 'rotate(360deg)' },
				],
				{
					duration: 700,
					iterations: Number.POSITIVE_INFINITY,
					easing: 'linear',
				},
			);
			return;
		}

		spinnerAnimation?.cancel();
		spinnerAnimation = null;
	};

	const showRevision = (card) => {
		const payload = getRevisionPayload(card);
		if (!payload) {
			return;
		}

		cards.forEach((c) => c.classList.remove('is-active'));
		card.classList.add('is-active');

		activeRestoreUrl = payload.restoreUrl || '';
		activeRestoreId = String(payload.restoreId || '');
		activeRevisionIsCurrent = Boolean(payload.isCurrent);
		previewLabel.textContent = payload.label || payload.title || 'Version';
		restoreConfirmBtn.hidden = activeRevisionIsCurrent;
		restoreConfirmBtn.disabled = activeRevisionIsCurrent;

		emptyPanel.hidden = true;
		activePanel.hidden = false;
		setPreviewLoading(true);

		const currentToken = ++previewLoadToken;
		window.requestAnimationFrame(() => {
			writeRevisionFrame(payload.content || '', payload.title || '', payload.excerpt || '', payload.template || 'default');

			window.setTimeout(() => {
				if (currentToken !== previewLoadToken) {
					return;
				}

				setPreviewLoading(false);
			}, 220);
		});
	};

	cards.forEach((card) => {
		const trigger = card.querySelector('[data-revision-trigger]');
		trigger?.addEventListener('click', () => showRevision(card));
	});

	restoreConfirmBtn.addEventListener('click', () => {
		if (!activeRestoreUrl || !activeRestoreId || activeRevisionIsCurrent) {
			return;
		}

		const confirmed = window.confirm('Aktuellen Inhalt wirklich durch diese Version ersetzen? Nicht gespeicherte Aenderungen gehen verloren.');
		if (confirmed) {
			submitRevisionRestore(activeRestoreUrl, activeRestoreId, csrfToken);
		}
	});
};

document.addEventListener('DOMContentLoaded', initRevisionPreview);
