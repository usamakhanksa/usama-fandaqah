<template>
    <div>
        <nav v-if="crumbs.length">
            <ul class="breadcrumbs">
                <li class="breadcrumbs__item" v-for="(crumb,i) in crumbs" :key="i">
                    <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
                </li>
            </ul>
        </nav>
        <heading  class="mb-6">{{settings.name}}</heading>
        <div class="card">
            {{ settings }}
            <form @submit.prevent="updateSettings">

                <div v-for="(setting,i) in settings.items" :key="i">
                   
                    <component
                        v-bind:is="'form-' + setting.field.component"
                        v-bind:class="`form-${setting.field.textAlign}`"
                        v-bind:field="setting.field"
                        v-bind:v-model="setting.value"
                        ref="components"
                    />
                </div>
                <div class="bg-30 flex px-8 py-4">
                    <button type="submit" class="ml-auto btn btn-default btn-primary mr-3">
                        {{ translations['save_settings'] }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                settings: [],
                installed: false,
                reset: true,
                groupName: 'general',
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
                    text: 'General',
                    to: '#',
                }
            ]

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
            updateSettings() {
                let values = this.obtainValues();
                Nova.request()
                    .put("/nova-vendor/settings/update", {values: values, group: this.groupName})
                    .then(response => {
                      //  console.log("response.data", response.data)
                        this.settings = response.data.settings
                        let message = response.data.message && response.data.message !== ''
                            ? response.data.message
                            : this.translations['save_success'];
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
    .tab-icon {
        margin-right: 0.4rem;
        height: 0.8rem;
    }
</style>
