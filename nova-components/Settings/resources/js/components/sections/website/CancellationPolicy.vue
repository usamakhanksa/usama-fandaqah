<template>
    <div>
        <div class="flex w-full mb-4">
            <nav v-if="crumbs.length">
                <ul class="breadcrumbs">
                    <li class="breadcrumbs__item" v-for="crumb in crumbs" v-if="crumb.text != false">
                        <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
                    </li>
                </ul>
            </nav>
        </div>
        <card id="domain_website">
            <div class="title-card title-card p-3 bg-gray-200 text-xl rounded-lg rounded-b-none border-b-2  border-gray-300">{{__('Terms & Conditions settings')}}</div>
            <div class="card-content">
                <form @submit.prevent="updateSettings">

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="cancellation_policy_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Terms & Conditions AR')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <vue-editor   ref="editorAr" id="cancellation_policy_ar" :editor-toolbar="customToolbar" v-model="cancellation_policy_ar"></vue-editor>
<!--                                    <textarea rows="20" cols="50" id="cancellation_policy_ar" v-model="cancellation_policy_ar" class="inline-block form-control form-input form-input-bordered text-lg text-90">-->
<!--                                    </textarea>-->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="cancellation_policy_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Terms & Conditions EN')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <vue-editor ref="editorEn" id="cancellation_policy_en" :editor-toolbar="customToolbar" v-model="cancellation_policy_en"></vue-editor>

<!--                                    <textarea rows="20" cols="50" id="cancellation_policy_en" v-model="cancellation_policy_en" class="inline-block form-control form-input form-input-bordered text-lg text-90">-->
<!--                                    </textarea>-->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-30 flex justify-between px-4 py-4" :class="locale == 'ar' ? ' justify-end' : ' justify-start'">
                        <button type="submit" class="btn bg-blue-500 text-white py-2 px-10 hover:bg-blue-400">
                            {{ __('Save') }}
                        </button>
                        <button type="button" @click="goBack" class="btn bg-gray-600 text-white py-2 px-10 hover:bg-gray-500">{{ __('Back') }}</button>
                    </div>
                </form>
            </div>
        </card>
    </div>
</template>

<script>
    import { VueEditor } from "vue2-editor";

    export default {
        name: "CancellationPolicy",
        components: {
            VueEditor,
        },
        data() {
            return {
                customToolbar: [
                    ["bold", "italic", "underline"],
                    [{ list: "ordered" }, { list: "bullet" }] ,
                    [
                        {
                            align: ""
                        }, {
                        align: "center"
                    }, {
                        align: "right"
                    }, {
                        align: "justify"
                    }
                    ]
                ],
                cancellation_policy_ar: null,
                cancellation_policy_en: null,
                team: Object,
                crumbs: [],
                locale : null
            }
        },
        mounted() {
            // this.$refs.editorAr.quill.format('align', 'right');
            // this.$refs.editorEn.quill.format('align', 'left');
            // this.$refs.editorAr.quill.format('direction', 'rtl');
            // this.$refs.editorEn.quill.format('direction', 'ltr');
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
                    text: 'Website Settings',
                    to: '/settings/website',
                },
                {
                    text: 'Terms & Conditions settings',
                    to: '#',
                }
            ];
            this.team = Spark.state.currentTeam;
            this.getSettings();
        },
        methods: {
            updateSettings() {
                Nova.request()
                    .put("/nova-vendor/settings/update-website-settings/"+this.team.id, {
                        cancellation_policy: {
                            ar: this.cancellation_policy_ar,
                            en: this.cancellation_policy_en
                        },
                    })
                    .then(response => {
                        this.settings = response.data;
                        this.$router.push('/settings/website');
                        this.$toasted.success(Nova.app.__('Success'), {
                            duration: 3000
                        })
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            getSettings() {
                Nova.request()
                    .get("/nova-vendor/settings/website-settings/"+this.team.id, {})
                    .then(response => {
                        this.settings = response.data;

                        this.cancellation_policy_ar = this.settings.cancellation_policy.ar ? this.settings.cancellation_policy.ar : '';
                        this.cancellation_policy_en = this.settings.cancellation_policy.en ? this.settings.cancellation_policy.en : '';

                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            goBack() {
                this.$router.push({path: '/settings/website'})
            }
        },
        created() {
            this.locale = Nova.config.local ;
        },
    }
</script>

<style scoped>

</style>
