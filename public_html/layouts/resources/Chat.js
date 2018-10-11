/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.Chat_Js = class Chat_Js {
	/**
	 * Get instance of Chat_Js.
	 * @returns {Chat_Js|Window.Chat_Js}
	 */
	static getInstance() {
		if (typeof Chat_Js.instance === 'undefined') {
			Chat_Js.instance = new Chat_Js();
		}
		return Chat_Js.instance;
	}

	/**
	 * Constructor of class.
	 */
	constructor() {
		this.chatRoom = [];
	}

	/**
	 * Add new item to room list.
	 * @param {int} roomId
	 * @param {string} name
	 */
	addRoomItem(roomId, name) {
		let template = $('.js-chat-modal .js-room-template');
		if (template.length) {
			let item = template.clone(false, false);
			item.removeClass('hide');
			item.removeClass('js-room-template');
			item.removeClass('fontBold');
			item.find('.row').html(name);
			item.data('roomId', roomId);
			$('.js-chat-modal .js-chat-rooms-list').append(item);
			this.registerSwitchRoom($('.js-chat-modal'));
		}
	}

	/**
	 * Register switch room.
	 * @param {jQuery} container
	 */
	registerSwitchRoom(container) {
		container.find('.js-change-room').off('click').on('click', (e) => {
			let chatModal = $(e.currentTarget).closest('.js-chat-modal');
			let roomId = $(e.currentTarget).data('roomId');
			const progressIndicatorElement = $.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			AppConnector.request({
				dataType: 'json',
				data: {
					module: 'Chat',
					action: 'Entries',
					mode: 'switchRoom',
					chat_room_id: roomId
				}
			}).done((dataResult) => {
				let chatRoom = container.find('.js-chat-items');
				if (typeof dataResult === 'undefined') {
					chatRoom.html('');
				} else {
					chatRoom.html(dataResult.result.html);
				}
				chatRoom.animate({scrollTop: chatRoom.get(0).scrollHeight});
				let prevChatRoomId = container.data('chatRoomId');
				container.data('chatRoomId', roomId);
				chatModal.find('.js-change-room').each((index, element) => {
					if (roomId == $(element).data('roomId')) {
						$(element).removeClass('fontBold').addClass('fontBold');
						container.find('.js-chat-items')
							.removeClass('js-chat-room-' + prevChatRoomId)
							.addClass('js-chat-room-' + roomId);
					} else {
						$(element).removeClass('fontBold');
					}
				});
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
			}).fail((error, err) => {
				app.errorLog(error, err);
			});
		});
	}

	/**
	 * Add chat room to record.
	 *
	 * @param {jQuery} container
	 */
	addRoom(container) {
		const chatRoomId = container.data('chatRoomId');
		if (typeof chatRoomId !== 'undefined') {
			const self = this;
			AppConnector.request({
				module: 'Chat',
				action: 'Entries',
				mode: 'addRoom',
				record: chatRoomId,
			}).done((data) => {
				if (data && data.success) {
					if (container.find('.js-container-button').hasClass('hide')) {
						container.find('.js-container-button').removeClass('hide');
						container.find('.js-container-items').addClass('hide');
					} else {
						container.find('.js-container-button').addClass('hide');
						container.find('.js-container-items').removeClass('hide');
					}
					self.addRoomItem(data.result.chat_room_id, data.result.name);
				}
			}).fail((error, err) => {
				app.errorLog(error, err);
			});
		} else {
			app.errorLog(new Error("Unknown chat room id"));
		}
	}

	/**
	 * Update chat.
	 * @param {int} chatRoomId
	 * @param {html} html
	 */
	updateChat(container, chatRoomId, html) {
		if (html) {
			let chatRoom = container.find('.js-chat-room-' + chatRoomId);
			chatRoom.append(html);
			chatRoom.animate({scrollTop: chatRoom.get(0).scrollHeight});
		}
	}

	/**
	 * Send chat message.
	 * @param {jQuery} container
	 * @param {jQuery} inputMessage
	 */
	sendMessage(container, inputMessage) {
		if (inputMessage.val() == '') {
			return;
		}
		const chatRoomId = container.data('chatRoomId');
		if (typeof chatRoomId !== 'undefined') {
			const self = this;
			const chatItems = container.find('.js-chat-items');
			let icon = container.find('.modal-title .fa-comments');
			icon.css('color', '#00e413');
			AppConnector.request({
				dataType: 'json',
				data: {
					module: 'Chat',
					action: 'Entries',
					mode: 'addMessage',
					message: inputMessage.val(),
					cid: chatItems.find('.chatItem').last().data('cid'),
					chat_room_id: chatRoomId
				}
			}).done((dataResult) => {
				self.updateChat(container, chatRoomId, dataResult.result.html);
				inputMessage.val("");
				icon.css('color', '#000');
				if (dataResult.result['user_added_to_room']) {
					self.addRoomItem(dataResult.result['room']['room_id'], dataResult.result['room']['name']);
				}
			}).fail((error, err) => {
				app.errorLog(error, err);
			});
		} else {
			app.errorLog(new Error("Unknown chat room id"));
		}
	}

	/**
	 * Get chat items.
	 * @param {jQuery} container
	 */
	getChatItems(container) {
		const chatRoomId = container.data('chatRoomId');
		const chatItems = container.find('.js-chat-items');
		const self = this;
		if (typeof chatRoomId !== 'undefined') {
			AppConnector.request({
				dataType: 'json',
				data: {
					module: 'Chat',
					view: 'Entries',
					mode: 'get',
					cid: chatItems.find('.chatItem').last().data('cid'),
					chat_room_id: chatRoomId
				}
			}).done((dataResult) => {
				if (dataResult.result.success) {
					if (
						dataResult.result['room_id'] == chatRoomId &&
						container.find('.js-container-button').length &&
						!container.find('.js-container-button').hasClass('hide')
					) {
						container.find('.js-container-button').addClass('hide');
						container.find('.js-container-items').removeClass('hide');
					}
					self.updateChat(container, chatRoomId, dataResult.result.html);
				}
			}).fail((error, err) => {
				clearTimeout(self.chatRoom[container.data('chatRoomIdx')]);
			});
		} else {
			app.errorLog(new Error("Unknown chat room id"));
		}
	}

	/**
	 * Register chat load items.
	 * @param {jQuery} container
	 */
	registerChatLoadItems(container) {
		const self = this;
		self.chatRoom[container.data('chatRoomIdx')] = setTimeout(() => {
			self.getChatItems(container);
			self.registerChatLoadItems(container);
		}, container.data('timer'));
	}

	/**
	 * Register header link chat
	 */
	registerHeaderLinkChat() {
		$('.headerLinkChat').on('click', (e) => {
			e.stopPropagation();
			let remindersNoticeContainer = $('.remindersNoticeContainer,.remindersNotificationContainer');
			if (remindersNoticeContainer.hasClass('toggled')) {
				remindersNoticeContainer.removeClass('toggled');
			}
			$('.actionMenu').removeClass('actionMenuOn');
			$('.chatModal').modal({backdrop: false});
		});
	}

	registerChatCheck(timer, container) {
		const self = this;
		self.chatCheckTimer = setTimeout(() => {
			//self.getChatItems(container);
			self.registerChatCheck(timer, container);
		}, timer);
	}

	/**
	 * Register chat events
	 * @param {jQuery} container
	 */
	registerEvents(container = $('.js-chat-modal')) {
		console.log('registerEvents: ' + container.length);
		if (container.length) {
			const self = this;
			container.data('chat-room-idx', self.chatRoom.length);
			container.find('.js-create-chatroom').on('click', (e) => {
				self.addRoom(container);
			});
			container.find('.js-chat-message').on('keydown', (e) => {
				if (e.keyCode === 13) {
					e.preventDefault();
					self.sendMessage(container, $(e.currentTarget));
					return false;
				}
			});
			self.registerChatLoadItems(container);
			let modal = container.closest('.chatModal');
			if (modal.length) {
				self.registerSwitchRoom(container);
				self.registerHeaderLinkChat();
				app.showNewScrollbar(modal.find('.modal-body'), {wheelPropagation: true});
				app.animateModal(modal, 'slideInRight', 'slideOutRight');
			}
		}
	}
}
/**
 * Create chat instance and register events.
 */
$(document).ready((e) => {
	const instance = Chat_Js.getInstance();
	instance.registerEvents();
});
