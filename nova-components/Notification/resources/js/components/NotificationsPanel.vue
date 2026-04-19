<template>
    <div class="text-white notifications-panel">

        <div class="flex notifications_header">
          <div class="notifications_header_item" :class="{'active': showingNotifications}" id="show-notifications" @click="showNotifications">{{__('Notifications')}}</div>
          <div class="notifications_header_item" :class="{'active': showingAnnouncements}" id="show-announcements" @click="showAnnouncements">{{__('Announcements')}}</div>
        </div>

        <div class="all_notifications" v-if="notifications">
            <div v-if="showingAnnouncements && hasAnnouncements">
                <div v-for="announcement in notifications.announcements" :key="announcement.id">
                    <notification-message :notification="announcement"></notification-message>
                </div>
            </div>

            <div v-if="showingNotifications && hasNotifications">
                <div v-for="notification in notifications.notifications" :key="notification.id">
                    <notification-message :notification="notification"></notification-message>
                </div>
            </div>
        </div>
        <div class="notifications-close-panel">
          <div id="notifications-panel-close" @click="toggleNotificationsPanel">{{__('Close')}}</div>
        </div>

    </div>
</template>

<script>
    import InfiniteLoading from 'vue-infinite-loading'
    export default {
        name: 'NotificationsPanel',
        props: ['notifications', 'hasUnreadAnnouncements', 'loadingNotifications'],
        components: {
            InfiniteLoading,
        },
        data () {
            return {
                showingNotifications: true,
                showingAnnouncements: false,
                interval: null,
                currentPage: 0
            }
        },
        methods: {
            toggleNotificationsPanel() {
                this.$emit('showNotifications')
            },
            /**
             * Show the user notifications.
             */
            showNotifications() {
                this.showingNotifications = true;
                this.showingAnnouncements = false;
            },
            /**
             * Show the product announcements.
             */
            showAnnouncements() {
                this.showingNotifications = false;
                this.showingAnnouncements = true;

                this.updateLastReadAnnouncementsTimestamp();
            },
            /**
             * Update the last read announcements timestamp.
             */
            updateLastReadAnnouncementsTimestamp() {
                axios.put('/user/last-read-announcements-at')
                    .then(() => {
                        Nova.$emit('updateUser');
                    });
            }
        },
        created () {

        },
        mounted() {
        },
        computed: {
            /**
             * Get the active notifications or announcements.
             */
            activeNotifications() {
                if ( ! this.notifications) {
                    return [];
                }

                if (this.showingNotifications) {
                    return this.notifications.notifications;
                } else {
                    return this.notifications.announcements;
                }
            },


            /**
             * Determine if the user has any notifications.
             */
            hasNotifications() {
                return this.notifications && this.notifications.notifications.length > 0;
            },


            /**
             * Determine if the user has any announcements.
             */
            hasAnnouncements() {
                return this.notifications && this.notifications.announcements.length > 0;
            }
        }
    }
</script>

<style scoped>
.notifications-panel {
  z-index: 99999;
  position: fixed;
  right: 0;
  top: 0;
  min-width: 340px;
  height: 100vh;
  background-color: #232a31;
  padding-bottom: 60px;
  -webkit-box-shadow: 0 0 20px 0px #232a31;
  box-shadow: 0 0 20px 0px #232a31;
}
html:lang(en) .notifications-panel {
  left: 0;
  right: auto;
}
.notifications-panel::after {
  content: "";
  position: fixed;
  right: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: #232a3199;
  z-index: 9;
}
.notifications_header {
  background: #3c434c;
  height: 50px;
  position: absolute;
  top: 0;
  right: 0;
  width: 100%;
  z-index: 9999999;
}
.notifications_header .notifications_header_item {
  width: 50%;
  text-align: center;
  height: 50px;
  line-height: 50px;
  cursor: pointer;
}
.notifications_header .notifications_header_item.active {
  background: #232a31;
  cursor: auto;
}
.notifications-panel .all_notifications {
  height: 100%;
  overflow-y: scroll;
  position: absolute;
  top: 0;
  width: 100%;
  right: 0;
  padding: 50px 0 60px 0;
  z-index: 99999;
  background: #232a31;
}
.notifications-close-panel {
  position: absolute;
  bottom: 0;
  right: 0;
  left: 0;
  background: #232a31;
  z-index: 99999;
}
#notifications-panel-close {
  background: #3d434b;
  margin: 10px;
  width: auto;
  border-radius: 4px;
  height: 40px;
  line-height: 40px;
  text-align: center;
  font-size: 17px;
  cursor: pointer;
}
#notifications-panel-close:hover {background: #4C525A;}
</style>
