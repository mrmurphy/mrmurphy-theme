/**
 * Microblog card actions: Like, Comment, Reblog.
 *
 * Wires up the three buttons on every [data-microblog-card] on the page,
 * hydrates like state and counts from the mrmurphy/v1 likes endpoint, and
 * manages two shared <dialog> elements populated on demand from
 * mrmurphy/v1 dialog / comments routes.
 */
(function () {
	'use strict';

	var ROOT = (window.mrmurphyMicroblog && window.mrmurphyMicroblog.root) || '/wp-json/mrmurphy/v1';
	var CLIENT_ID_KEY = 'mmb_client_id';
	var MAX_ID_LENGTH = 64;

	function getClientId() {
		var stored = null;
		try { stored = window.localStorage.getItem(CLIENT_ID_KEY); } catch (e) {}
		if (stored && typeof stored === 'string' && stored.length <= MAX_ID_LENGTH) {
			return stored;
		}
		var fresh = '';
		if (window.crypto && typeof window.crypto.randomUUID === 'function') {
			fresh = window.crypto.randomUUID();
		} else {
			fresh = 'c-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
		}
		try { window.localStorage.setItem(CLIENT_ID_KEY, fresh); } catch (e) {}
		return fresh;
	}

	function ready(fn) {
		if (document.readyState !== 'loading') fn();
		else document.addEventListener('DOMContentLoaded', fn);
	}

	/* ---------- Like ---------- */

	function applyLikeState(btn, count, liked) {
		var countEl = btn.querySelector('[data-mb-like-count]');
		if (countEl) {
			countEl.textContent = String(count);
			countEl.style.display = count === 0 ? 'none' : '';
		}
		btn.setAttribute('aria-pressed', liked ? 'true' : 'false');
		btn.classList.toggle('mb-action--liked', !!liked);
	}

	function pulse(btn) {
		btn.classList.remove('mb-action--like-pulse');
		void btn.offsetWidth;
		btn.classList.add('mb-action--like-pulse');
	}

	function bindLikeButton(btn, clientId) {
		btn.addEventListener('click', function () {
			var postId = parseInt(btn.getAttribute('data-post-id'), 10);
			if (!postId) return;
			var liked = btn.getAttribute('aria-pressed') === 'true';
			var nextAction = liked ? 'unlike' : 'like';
			var prevLiked = liked;
			var prevCount = parseInt(btn.querySelector('[data-mb-like-count]').textContent, 10) || 0;
			var optimisticCount = prevLiked ? prevCount - 1 : prevCount + 1;
			applyLikeState(btn, optimisticCount, !prevLiked);
			pulse(btn);

			fetch(ROOT + '/likes', {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': (window.mrmurphyMicroblog && window.mrmurphyMicroblog.nonce) || ''
				},
				body: JSON.stringify({
					post_id: postId,
					client_id: clientId,
					action: nextAction
				})
			}).then(function (r) {
				if (r.ok) return r.json();
				throw r;
			}).then(function (data) {
				applyLikeState(btn, data.count, data.liked);
			}).catch(function () {
				applyLikeState(btn, prevCount, prevLiked);
			});
		});
	}

	function hydrateLikes(cards, clientId) {
		var ids = cards.map(function (c) { return c.getAttribute('data-post-id'); }).filter(Boolean);
		if (!ids.length) return;
		fetch(ROOT + '/likes?post_ids=' + encodeURIComponent(ids.join(',')) + '&client_id=' + encodeURIComponent(clientId), {
			credentials: 'same-origin',
			headers: { 'X-WP-Nonce': (window.mrmurphyMicroblog && window.mrmurphyMicroblog.nonce) || '' }
		}).then(function (r) { return r.json(); }).then(function (data) {
			cards.forEach(function (card) {
				var id = card.getAttribute('data-post-id');
				var btn = card.querySelector('[data-mb-like]');
				if (!btn || !data.likes[id]) return;
				applyLikeState(btn, data.likes[id].count, data.likes[id].liked);
			});
		}).catch(function () {});
	}

	/* ---------- Dialog plumbing ---------- */

	function openDialog(id, html) {
		var dlg = document.getElementById(id);
		if (!dlg) return;
		var slot = dlg.querySelector('[data-mb-dialog-content]');
		if (slot) slot.innerHTML = html;

		// Cancel any in-flight close animation before (re)opening.
		dlg.classList.remove('mb-dialog--closing');
		dlg.__mbClosing = false;
		if (dlg.__mbCloseTimer) {
			clearTimeout(dlg.__mbCloseTimer);
			dlg.__mbCloseTimer = null;
		}

		if (typeof dlg.showModal === 'function') {
			if (dlg.open) dlg.close();
			dlg.showModal();
		} else {
			dlg.setAttribute('open', '');
		}
	}

	function closeDialog(id) {
		var dlg = document.getElementById(id);
		if (!dlg || dlg.__mbClosing || !dlg.open) return;

		dlg.__mbClosing = true;
		dlg.classList.add('mb-dialog--closing');

		var done = function () {
			dlg.classList.remove('mb-dialog--closing');
			if (typeof dlg.close === 'function') {
				dlg.close();
			} else {
				dlg.removeAttribute('open');
			}
			dlg.__mbClosing = false;
			dlg.removeEventListener('animationend', done);
			if (dlg.__mbCloseTimer) {
				clearTimeout(dlg.__mbCloseTimer);
				dlg.__mbCloseTimer = null;
			}
		};

		dlg.addEventListener('animationend', done);
		// Fallback if the animation event does not fire.
		dlg.__mbCloseTimer = setTimeout(done, 300);
	}

	function wireDialogCloseHandlers() {
		['mmb-comment-dialog', 'mmb-share-dialog'].forEach(function (id) {
			var dlg = document.getElementById(id);
			if (!dlg || dlg.__mbWired) return;
			dlg.__mbWired = true;
			dlg.addEventListener('click', function (e) {
				if (e.target === dlg) closeDialog(id);
			});
			document.addEventListener('click', function (e) {
				var closer = e.target.closest('[data-mb-dialog-close]');
				if (closer && dlg.contains(closer)) closeDialog(id);
			});
		});
	}

	function fetchDialog(route, postId, openId) {
		fetch(ROOT + route + '?post_id=' + encodeURIComponent(postId), {
			credentials: 'same-origin',
			headers: { 'X-WP-Nonce': (window.mrmurphyMicroblog && window.mrmurphyMicroblog.nonce) || '' }
		}).then(function (r) { return r.json(); }).then(function (data) {
			openDialog(openId, data.html || '');
		}).catch(function () {
			openDialog(openId, '<p class="mb-dialog__error">Couldn’t load this post. Please try again.</p>');
		});
	}

	/* ---------- Comment ---------- */

	function bindCommentButton(btn) {
		btn.addEventListener('click', function () {
			var postId = parseInt(btn.getAttribute('data-post-id'), 10);
			if (!postId) return;
			fetchDialog('/dialog/comment', postId, 'mmb-comment-dialog');
		});
	}

	function bindDynamicCommentHandlers(scope) {
		scope = scope || document;

		scope.querySelectorAll('[data-mb-comment-form]').forEach(function (form) {
			if (form.__mbWired) return;
			form.__mbWired = true;

			form.addEventListener('submit', function (e) {
				e.preventDefault();
				var errEl = form.querySelector('[data-mb-comment-error]');
				if (errEl) errEl.textContent = '';
				var body = new FormData(form);
				body.append('post_id', form.getAttribute('data-post-id'));
				fetch(ROOT + '/comments', {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'X-WP-Nonce': (window.mrmurphyMicroblog && window.mrmurphyMicroblog.nonce) || '' },
					body: body
				}).then(function (r) { return r.json(); }).then(function (data) {
					if (data && data.comment) {
						appendComment(form, data.comment);
						form.reset();
						incrementCommentCount(form.getAttribute('data-post-id'));
					} else {
						var msg = data && (data.message || (data.code)) ? (data.message || data.code) : 'Comment failed.';
						if (errEl) errEl.textContent = msg;
					}
				}).catch(function () {
					if (errEl) errEl.textContent = 'Network error. Please try again.';
				});
			});
		});

		scope.querySelectorAll('[data-mb-comment-list]').forEach(function (list) {
			if (list.__mbWired) return;
			list.__mbWired = true;
			loadComments(list);
		});
	}

	function appendComment(form, comment) {
		var list = document.querySelector('[data-mb-comment-list]');
		if (!list) return;
		var loading = list.querySelector('.mb-dialog__comments-loading');
		if (loading) loading.remove();
		var empty = list.querySelector('.mb-dialog__comments-empty');
		if (empty) empty.remove();
		list.appendChild(buildCommentNode(comment));
	}

	function incrementCommentCount(postId) {
		document.querySelectorAll('.mb-action--comment[data-post-id="' + postId + '"]').forEach(function (btn) {
			var el = btn.querySelector('[data-mb-comment-count]');
			if (!el) return;
			var n = parseInt(el.textContent, 10) || 0;
			el.textContent = String(n + 1);
			el.style.display = '';
		});
	}

	function buildCommentNode(c) {
		var node = document.createElement('div');
		node.className = 'mb-comment';
		if (c.pending) node.classList.add('mb-comment--pending');
		var author = document.createElement('span');
		author.className = 'mb-comment__author';
		author.textContent = c.author || 'You';
		var time = document.createElement('span');
		time.className = 'mb-comment__time';
		time.textContent = c.date || '';
		if (c.pending) {
			var note = document.createElement('em');
			note.textContent = ' — pending moderation';
			time.appendChild(note);
		}
		var body = document.createElement('div');
		body.className = 'mb-comment__text';
		body.innerHTML = c.content || '';
		node.appendChild(author);
		node.appendChild(time);
		node.appendChild(body);
		return node;
	}

	function loadComments(list) {
		var postId = list.getAttribute('data-post-id');
		if (!postId) return;
		fetch(ROOT + '/comments?post_id=' + encodeURIComponent(postId), {
			credentials: 'same-origin'
		}).then(function (r) { return r.json(); }).then(function (data) {
			var loading = list.querySelector('.mb-dialog__comments-loading');
			if (loading) loading.remove();
			(data.comments || []).forEach(function (c) {
				list.appendChild(buildCommentNode(c));
			});
			if (!data.comments || !data.comments.length) {
				var empty = document.createElement('p');
				empty.className = 'mb-dialog__comments-empty';
				empty.textContent = 'No comments yet. Be the first.';
				list.appendChild(empty);
			}
		}).catch(function () {
			var loading = list.querySelector('.mb-dialog__comments-loading');
			if (loading) loading.textContent = 'Couldn’t load comments.';
		});
	}

	/* ---------- Reblog ---------- */

	function bindReblogButton(btn) {
		btn.addEventListener('click', function () {
			var postId = parseInt(btn.getAttribute('data-post-id'), 10);
			if (!postId) return;
			fetchDialog('/dialog/share', postId, 'mmb-share-dialog');
		});
	}

	function bindDynamicShareHandlers() {
		var dlg = document.getElementById('mmb-share-dialog');
		if (!dlg) return;
		if (dlg.__mbShareWired) return;
		dlg.__mbShareWired = true;
		document.addEventListener('click', function (e) {
			var copy = e.target.closest('[data-mb-copy-link]');
			if (!copy) return;
			e.preventDefault();
			var url = copy.getAttribute('data-permalink');
			var status = copy.querySelector('[data-mb-copy-status]');
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(url).then(function () {
					if (status) { status.textContent = 'Link copied'; }
					setTimeout(function () { if (status) status.textContent = '→'; }, 2000);
				});
			} else if (status) {
				status.textContent = 'Copy this link: ' + url;
			}
		});
	}

	/* ---------- Init ---------- */

	ready(function () {
		var clientId = getClientId();
		var cards = Array.prototype.slice.call(document.querySelectorAll('[data-microblog-card]'));
		if (!cards.length) return;

		cards.forEach(function (card) {
			var likeBtn = card.querySelector('[data-mb-like]');
			var commentBtn = card.querySelector('[data-mb-comment]');
			var reblogBtn = card.querySelector('[data-mb-reblog]');
			if (likeBtn) bindLikeButton(likeBtn, clientId);
			if (commentBtn) bindCommentButton(commentBtn);
			if (reblogBtn) bindReblogButton(reblogBtn);
		});

		hydrateLikes(cards, clientId);
		wireDialogCloseHandlers();
		bindDynamicShareHandlers();

		// Wire form/comment-list handlers after dialog content is injected.
		var observer = new MutationObserver(function (mutations) {
			mutations.forEach(function (m) {
				m.addedNodes.forEach(function (node) {
					if (node.nodeType !== 1) return;
					bindDynamicCommentHandlers(node);
				});
			});
		});
		['mmb-comment-dialog', 'mmb-share-dialog'].forEach(function (id) {
			var dlg = document.getElementById(id);
			if (dlg) observer.observe(dlg, { childList: true, subtree: true });
		});
	});
})();