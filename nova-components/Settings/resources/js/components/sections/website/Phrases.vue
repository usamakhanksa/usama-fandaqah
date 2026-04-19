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
        <card id="website_phrases">
            <div class="title-card title-card p-3 bg-gray-200 text-xl rounded-lg rounded-b-none border-b-2  border-gray-300">{{__('Basic phrases')}}</div>
            <div class="card-content">
                <form @submit.prevent="updatePhrases">

<!--                    <div class="form-left">-->
<!--                        <div class="flex border-b border-40">-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/3 py-4 px-2">-->
<!--                                    <label for="slide_first_text_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Slider First Text Ar')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-4 px-2 w-1/2">-->
<!--                                    <input id="slide_first_text_ar" v-model="slide_first_text_ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/3 py-4 px-2">-->
<!--                                    <label for="slide_first_text_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Slider First Text En')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-4 px-2 w-1/2">-->
<!--                                    <input id="slide_first_text_en" v-model="slide_first_text_en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

<!--                    <div class="form-left">-->
<!--                        <div class="flex border-b border-40">-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/3 py-4 px-2">-->
<!--                                    <label for="slide_second_text_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Slider Second Text Ar')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-4 px-2 w-1/2">-->
<!--                                    <input id="slide_second_text_ar" v-model="slide_second_text_ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/3 py-4 px-2">-->
<!--                                    <label for="slide_second_text_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Slider Second Text En')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-4 px-2 w-1/2">-->
<!--                                    <input id="slide_second_text_en" v-model="slide_second_text_en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="title_first_text_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Title First Text Ar')}}</label>
                                </div>
                                <div class="py-4 px-2 w-1/2">
                                    <input id="title_first_text_ar" v-model="title_first_text_ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="title_first_text_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Title First Text En')}}</label>
                                </div>
                                <div class="py-4 px-2 w-1/2">
                                    <input id="title_first_text_en" v-model="title_first_text_en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="title_second_text_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Title Second Text Ar')}}</label>
                                </div>
                                <div class="py-4 px-2 w-1/2">
                                    <input id="title_second_text_ar" v-model="title_second_text_ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="title_second_text_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Title Second Text En')}}</label>
                                </div>
                                <div class="py-4 px-2 w-1/2">
                                    <input id="title_second_text_en" v-model="title_second_text_en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="title_second_text_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Search Engine , Whats your destination AR')}}</label>
                                </div>
                                <div class="py-4 px-2 w-1/2">
                                    <input id="search_box_top_first_text_ar" v-model="search_box_top_first_text_ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="title_second_text_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Search Engine , Whats your destination EN')}}</label>
                                </div>
                                <div class="py-4 px-2 w-1/2">
                                    <input id="search_box_top_first_text_en" v-model="search_box_top_first_text_en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="title_second_text_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Search Engine , Select Hotel AR')}}</label>
                                </div>
                                <div class="py-4 px-2 w-1/2">
                                    <input id="search_box_top_second_text_ar" v-model="search_box_top_second_text_ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="title_second_text_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Search Engine , Select Hotel EN')}}</label>
                                </div>
                                <div class="py-4 px-2 w-1/2">
                                    <input id="search_box_top_second_text_en" v-model="search_box_top_second_text_en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="title_second_text_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Search Engine , ٍ Make an appropriate appointment AR')}}</label>
                                </div>
                                <div class="py-4 px-2 w-1/2">
                                    <input  v-model="search_box_top_third_text_ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="title_second_text_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Search Engine , ٍ Make an appropriate appointment EN')}}</label>
                                </div>
                                <div class="py-4 px-2 w-1/2">
                                    <input   v-model="search_box_top_third_text_en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="title_second_text_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Search button text Ar')}}</label>
                                </div>
                                <div class="py-4 px-2 w-1/2">
                                    <input id="search_button_text_ar" v-model="search_button_text_ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="search_button_text_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Search button text En')}}</label>
                                </div>
                                <div class="py-2 px-4 w-1/2">
                                    <input id="search_button_text_en" v-model="search_button_text_en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="rights_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Rights text Ar')}}</label>
                                </div>
                                <div class="py-4 px-2 w-1/2">
                                    <input id="rights_ar" v-model="rights_ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="rights_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Rights text En')}}</label>
                                </div>
                                <div class="py-4 px-2 w-1/2">
                                    <input id="rights_en" v-model="rights_en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                        </div>
                    </div>

<!--                    <div class="form-left">-->
<!--                        <div class="flex border-b border-40">-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/3 py-4 px-2">-->
<!--                                    <label for="rights_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Description Block Text Ar')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-4 px-2 w-1/2">-->
<!--                                    <input id="description_block_text_ar" v-model="description_block_text_ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/3 py-4 px-2">-->
<!--                                    <label for="description_block_text_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Description Block Text En')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-4 px-2 w-1/2">-->
<!--                                    <input id="description_block_text_en" v-model="description_block_text_en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

<!--                    <div class="form-left">-->
<!--                        <div class="flex border-b border-40">-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/3 py-4 px-2">-->
<!--                                    <label for="rights_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Images Block Text Ar')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-4 px-2 w-1/2">-->
<!--                                    <input id="images_block_text_ar" v-model="images_block_text_ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/3 py-4 px-2">-->
<!--                                    <label for="images_block_text_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Images Block Text En')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-4 px-2 w-1/2">-->
<!--                                    <input id="images_block_text_en" v-model="images_block_text_en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

<!--                    <div class="form-left">-->
<!--                        <div class="flex border-b border-40">-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/3 py-4 px-2">-->
<!--                                    <label for="rights_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Video Block Text Ar')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-4 px-2 w-1/2">-->
<!--                                    <input id="video_block_text_ar" v-model="video_block_text_ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/3 py-4 px-2">-->
<!--                                    <label for="video_block_text_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Video Block Text En')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-4 px-2 w-1/2">-->
<!--                                    <input id="video_block_text_en" v-model="video_block_text_en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="rights_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Special Features Block Text Ar')}}</label>
                                </div>
                                <div class="py-4 px-2 w-1/2">
                                    <input id="special_features_block_text_ar" v-model="special_features_block_text_ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="special_features_block_text_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Special Features Block Text En')}}</label>
                                </div>
                                <div class="py-4 px-2 w-1/2">
                                    <input id="special_features_block_text_en" v-model="special_features_block_text_en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="rights_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('General Features Block Text Ar')}}</label>
                                </div>
                                <div class="py-4 px-2 w-1/2">
                                    <input id="general_features_block_text_ar" v-model="general_features_block_text_ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-4 px-2">
                                    <label for="general_features_block_text_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('General Features Block Text En')}}</label>
                                </div>
                                <div class="py-4 px-2 w-1/2">
                                    <input id="general_features_block_text_en" v-model="general_features_block_text_en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                        </div>
                    </div>

<!--                    <div class="form-left">-->
<!--                        <div class="flex border-b border-40">-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/3 py-4 px-2">-->
<!--                                    <label for="options_block_text_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Options Features Block Text Ar')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-4 px-2 w-1/2">-->
<!--                                    <input id="options_block_text_ar" v-model="options_block_text_ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/3 py-4 px-2">-->
<!--                                    <label for="options_block_text_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Options Features Block Text En')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-4 px-2 w-1/2">-->
<!--                                    <input id="options_block_text_en" v-model="options_block_text_en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

<!--                    <div class="form-left">-->
<!--                        <div class="flex border-b border-40">-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/3 py-4 px-2">-->
<!--                                    <label for="note_for_waiting_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Note For Waiting Reservation Ar')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-4 px-2 w-1/2">-->
<!--                                    <textarea name="note_for_waiting_ar" v-model="note_for_waiting_ar" id="note_for_waiting_ar" cols="30" rows="10" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full h-40"></textarea>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/3 py-4 px-2">-->
<!--                                    <label for="note_for_waiting_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Note For Waiting Reservation En')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-4 px-2 w-1/2">-->
<!--                                    <textarea name="note_for_waiting_en" v-model="note_for_waiting_en" id="note_for_waiting_en" cols="30" rows="10" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full h-40"></textarea>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

<!--                    <div class="form-left">-->
<!--                        <div class="flex border-b border-40">-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/3 py-4 px-2">-->
<!--                                    <label for="view_all_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('View All in Home Ar')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-4 px-2 w-1/2">-->
<!--                                    <input type="text" name="view_all_ar" v-model="view_all_ar" id="view_all_ar" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full h-40">-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/3 py-4 px-2">-->
<!--                                    <label for="view_all_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('View All in Home En')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-4 px-2 w-1/2">-->
<!--                                    <input type="text" name="view_all_en" v-model="view_all_en" id="view_all_en" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full h-40">-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->


                    <div class="bg-30 flex p-4 justify-between">
                        <button type="submit" class="btn bg-blue-500 hover:bg-blue-400 text-white py-2 px-8">
                            {{ __('Save') }}
                        </button>
                        <button type="button" @click="goBack" class="btn bg-gray-600 hover:bg-gray-500 text-white py-2 px-8">{{ __('Back') }}</button>
                    </div>
                </form>
            </div>
        </card>
    </div>
</template>

<script>
    export default {
        name: "Phrases",
        data() {
            return {
                team: Object,
                settings: Object,
                crumbs: [],
                locale : null,
                view_all_ar : null,
                view_all_en : null,
                slide_first_text_ar: null,
                slide_first_text_en: null,
                slide_second_text_ar: null,
                slide_second_text_en: null,
                title_first_text_ar: null,
                title_first_text_en: null,
                title_second_text_ar: null,
                title_second_text_en: null,
                search_box_top_first_text_ar: null,
                search_box_top_first_text_en: null,
                search_box_top_second_text_ar: null,
                search_box_top_second_text_en: null,
                search_box_top_third_text_ar: null,
                search_box_top_third_text_en: null,
                search_button_text_ar: null,
                search_button_text_en: null,
                rights_ar: null,
                rights_en: null,
                description_block_text_ar: null,
                description_block_text_en: null,
                images_block_text_ar: null,
                images_block_text_en: null,
                video_block_text_ar: null,
                video_block_text_en: null,
                special_features_block_text_ar: null,
                special_features_block_text_en: null,
                general_features_block_text_ar: null,
                general_features_block_text_en: null,
                options_block_text_ar: null,
                options_block_text_en: null,
                note_for_waiting_ar: null,
                note_for_waiting_en: null,
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
                    text: 'Website Settings',
                    to: '/settings/website',
                },
                {
                    text: 'Basic phrases',
                    to: '#',
                }
            ];
            this.team = Spark.state.currentTeam;
            this.getSettings();
        },
        methods: {
            getSettings() {
                Nova.request()
                    .get("/nova-vendor/settings/website-settings/"+this.team.id, {})
                    .then(response => {
                        this.settings = response.data;
                        // this.slide_first_text_ar = this.settings.slide_first_text.ar;
                        // this.slide_first_text_en = this.settings.slide_first_text.en;
                        // this.slide_second_text_ar = this.settings.slide_second_text.ar;
                        // this.slide_second_text_en = this.settings.slide_second_text.en;
                        this.title_first_text_ar = this.settings.title_first_text.ar;
                        this.title_first_text_en = this.settings.title_first_text.en;
                        this.title_second_text_ar = this.settings.title_second_text.ar;
                        this.title_second_text_en = this.settings.title_second_text.en;
                        this.search_box_top_first_text_ar = this.settings.search_box_top_first_text.ar;
                        this.search_box_top_first_text_en = this.settings.search_box_top_first_text.en;
                        this.search_box_top_second_text_ar = this.settings.search_box_top_second_text.ar;
                        this.search_box_top_second_text_en = this.settings.search_box_top_second_text.en;
                        this.search_box_top_third_text_ar = this.settings.search_box_top_third_text.ar;
                        this.search_box_top_third_text_en = this.settings.search_box_top_third_text.en;
                        this.search_button_text_ar = this.settings.search_button_text.ar;
                        this.search_button_text_en = this.settings.search_button_text.en;
                        this.rights_ar = this.settings.rights.ar;
                        this.rights_en = this.settings.rights.en;
                        // this.description_block_text_ar = this.settings.description_block_text.ar;
                        // this.description_block_text_en = this.settings.description_block_text.en;
                        // this.images_block_text_ar = this.settings.images_block_text.ar;
                        // this.images_block_text_en = this.settings.images_block_text.en;
                        // this.video_block_text_ar = this.settings.video_block_text.ar;
                        // this.video_block_text_en = this.settings.video_block_text.en;
                        this.special_features_block_text_ar = this.settings.special_features_block_text.ar;
                        this.special_features_block_text_en = this.settings.special_features_block_text.en;
                        this.general_features_block_text_ar = this.settings.general_features_block_text.ar;
                        this.general_features_block_text_en = this.settings.general_features_block_text.en;
                        // this.options_block_text_ar = this.settings.options_block_text.ar;
                        // this.options_block_text_en = this.settings.options_block_text.en;
                        // this.note_for_waiting_ar = this.settings.note_for_waiting.ar;
                        // this.note_for_waiting_en = this.settings.note_for_waiting.en;
                        // this.view_all_ar = this.settings.view_all.ar;
                        // this.view_all_en = this.settings.view_all.en;
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            updatePhrases() {
                Nova.request()
                    .put("/nova-vendor/settings/update-website-settings/"+this.team.id, {
                        // slide_first_text: {
                        //     ar: this.slide_first_text_ar,
                        //     en: this.slide_first_text_en
                        // },
                        // slide_second_text: {
                        //     ar: this.slide_second_text_ar,
                        //     en: this.slide_second_text_en
                        // },
                        title_first_text: {
                            ar: this.title_first_text_ar,
                            en: this.title_first_text_en,
                        },
                        title_second_text: {
                            ar: this.title_second_text_ar,
                            en: this.title_second_text_en,
                        },
                        search_box_top_first_text: {
                            ar: this.search_box_top_first_text_ar,
                            en: this.search_box_top_first_text_en,
                        },
                        search_box_top_second_text: {
                            ar: this.search_box_top_second_text_ar,
                            en: this.search_box_top_second_text_en,
                        },
                        search_box_top_third_text: {
                            ar: this.search_box_top_third_text_ar,
                            en: this.search_box_top_third_text_en,
                        },

                        search_button_text: {
                            ar: this.search_button_text_ar,
                            en: this.search_button_text_en,
                        },
                        rights: {
                            ar: this.rights_ar,
                            en: this.rights_en,
                        },
                        // note_for_waiting: {
                        //     ar: this.note_for_waiting_ar,
                        //     en: this.note_for_waiting_en,
                        // },
                        // view_all: {
                        //     ar: this.view_all_ar,
                        //     en: this.view_all_en,
                        // },

                        // fixing not updated values
                        // description_block_text: {
                        //     ar: this.description_block_text_ar,
                        //     en: this.description_block_text_en,
                        // },
                        // images_block_text: {
                        //     ar: this.images_block_text_ar,
                        //     en: this.images_block_text_en,
                        // },
                        // video_block_text: {
                        //     ar: this.video_block_text_ar,
                        //     en: this.video_block_text_en,
                        // },
                        special_features_block_text: {
                            ar: this.special_features_block_text_ar,
                            en: this.special_features_block_text_en,
                        },
                        general_features_block_text: {
                            ar: this.general_features_block_text_ar,
                            en: this.general_features_block_text_en,
                        },
                        // options_block_text: {
                        //     ar: this.options_block_text_ar,
                        //     en: this.options_block_text_en,
                        // },
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
            goBack() {
                this.$router.push({path: '/settings/website'})
            }
        },
        created() {
            this.locale = Nova.config.local;
        },
    }
</script>

<style scoped>

</style>
