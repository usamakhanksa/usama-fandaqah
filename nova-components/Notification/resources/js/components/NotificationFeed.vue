<template>
    <div>
        <div @click="showNotifications" class="cursor-pointer notification-dropdown text-center notification-button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                <path class="heroicon-ui" d="M15 19a3 3 0 0 1-6 0H4a1 1 0 0 1 0-2h1v-6a7 7 0 0 1 4.02-6.34 3 3 0 0 1 5.96 0A7 7 0 0 1 19 11v6h1a1 1 0 0 1 0 2h-5zm-4 0a1 1 0 0 0 2 0h-2zm0-12.9A5 5 0 0 0 7 11v6h10v-6a5 5 0 0 0-4-4.9V5a1 1 0 0 0-2 0v1.1z" fill="#9fafbb"/>
            </svg>
            <div class="badge" v-show="notificationsCount > 0" style="color: white">
                {{ notificationsCount }}
            </div>
        </div>
        <div v-show="isNotificationsPanelVisible">
            <notifications-panel
                @showNotifications="showNotifications"
                :notifications="notifications"
                :has-unread-announcements="hasUnreadAnnouncements"
                :loading-notifications="loadingNotifications"
                >
            </notifications-panel>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'NovaNotifications',
        props: [
            'unreadAnnouncementsCount',
            'unreadNotificationsCount',
            'notifications',
            'hasUnreadAnnouncements',
            'loadingNotifications'
        ],
        data() {
            return {
                isNotificationsPanelVisible: false,
            }
        },
        methods: {
            showNotifications() {
                this.isNotificationsPanelVisible = !this.isNotificationsPanelVisible
                // Mark unread count as 0 when notifications panel is opened
                if (this.isNotificationsPanelVisible) {
                    Nova.$emit('showNotifications')
                }
            },
            showUnreadNotificationCount (unreadCount) {
                this.unreadCount = unreadCount
            },
            incrementUnreadCount () {
                this.unreadCount += 1
            }
        },
        computed:{
            notificationsCount(){
                return this.unreadAnnouncementsCount + this.unreadNotificationsCount;
            }
        }
    }
</script>

<style scoped>
.notification-button {
  position: relative;
  margin: 0 0 0 5px;
}
html:lang(en) .notification-button {margin: 0 50px 0 0;}
.notification-button .badge {
  background: red;
  border-radius: .25rem;
  padding: 0px 5px;
  position: absolute;
  color: #fff;
  top: -10px;
  right: -10px;
  font-size: 14px;
}
html:lang(en) .notification-button .badge {
  right: 0;
  left: -10px;
  padding: 0;
}
</style>
