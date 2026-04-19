<template>
    <div>
        <nav v-if="crumbs.length">
            <ul class="breadcrumbs">
                <li class="breadcrumbs__item" v-for="crumb in crumbs" v-if="crumb.text != false">
                    <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
                </li>
            </ul>
        </nav>
        <heading  class="mb-6">{{settings.name}}</heading>
        <div class="card">
            <form @submit.prevent="updateSettings">
                <div v-for="(setting,i) in settings.items" :key="i">
                    <template v-if="setting.field.attribute === 'enable_ratings_sms'">
                        <sms-toggle
                                v-bind:class="`form-${setting.field.textAlign}`"
                                v-bind:field="setting.field"
                                v-bind:v-model="setting.value"
                                ref="components"
                        />
                    </template>
                    <template v-else-if="setting.field.attribute === 'send_rating_after'">
                        <send-rating-after-component v-bind:class="`form-${setting.field.textAlign}`"
                                                     v-bind:field="setting.field"
                                                     v-bind:v-model="setting.value"
                                                     :settings="settings"
                                                     ref="components"
                        />
                    </template>
                    <template v-else>
                        <component

                                v-bind:is="'form-' + setting.field.component"
                                v-bind:class="`form-${setting.field.textAlign}`"
                                v-bind:field="setting.field"
                                v-bind:v-model="setting.value"
                                ref="components"
                        />
                    </template>

                </div>
                <div class="bg-30 flex p-4 justify-between">
                    <button type="submit" class="btn bg-blue-500 hover:bg-blue-400 text-white  py-2 px-8">
                        {{ translations['save_settings'] }}
                    </button>
                    <button type="button" @click="goBack" class="btn bg-gray-600 hover:bg-gray-500 text-white py-2 px-8">{{ __('Back') }}</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import CustomToggle from './CustomToggle';
    import SendRatingAfterComponent from './SendRatingAfterComponent';
    export default {
        name: "Ratings",
        components : {
         'sms-toggle' : CustomToggle,
            SendRatingAfterComponent
        },
        data() {
            return {
                settings: [],
                installed: false,
                reset: true,
                groupName: 'ratings',
                crumbs: [],
            }
        },
        mounted() {
            this.crumbs = [
                {
                    text: 'Home',
                    to: '/dashboards/main',
                },
                {
                    text: 'Settings',
                    to: '/settings',
                },
                {
                    text: 'Ratings',
                    to: '#',
                }
            ];
            this.getSettings();
        },
        updated() {
            let children = this.$refs.tabs && this.$refs.tabs.$children ? this.$refs.tabs.$children.length : 0;
            if (children > 0 && (this.$refs.tabs.activeTabHash === '' || this.reset === true)) {
                this.reset = false;
                let hash = this.$refs.tabs.tabs[0].hash;
                this.$refs.tabs.selectTab(hash);
            }
        },
        computed: {
            config: function () {
                return Nova.config.settings_tool.config;
            },
            translations: function () {
                return Nova.config.settings_tool.translations;
            }
        },
        methods: {
            getSettings() {
                Nova.request()
                    .get('/nova-vendor/settings/get/' + this.groupName).then(response => {
                    this.settings = response.data
                })
                    .catch(error => {
                        this.$toasted.error(this.translations['load_error'], {
                            duration: 3000
                        });
                    });
            },
            goBack() {
                this.$router.push({path: '/settings'})
            },
            updateSettings() {
                let values = this.obtainValues();
                if(values.send_rating_after == ""){
                    values.send_rating_after = 0;
                }
                Nova.request()
                    .put("/nova-vendor/settings/update", {values: values, group: this.groupName})
                    .then(response => {
                        this.settings = response.data.settings
                        let message = response.data.message && response.data.message !== ''
                            ? response.data.message
                            : this.translations['save_success'];
                        this.$router.push('/settings');
                        this.$toasted.success(message, {
                            duration: 3000
                        })
                    })
                    .catch(error => {
                        this.$toasted.error(this.translations['save_error'], {
                            duration: 3000
                        });
                    });
            },
            obtainValues() {
                let components = this.$refs.components ? this.$refs.components : [];
                let values = {};
                for (let index in components) {
                    values[components[index].fieldAttribute] = components[index].value;
                }
                return values;
            }
        },
    }
</script>

<style scoped>

</style>
