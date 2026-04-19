<template>
	<div id="Notes_page">
		<h1 class="mb-2 text-90 font-normal text-2xl">{{__('Staff feedback')}}</h1>


    <div class="add_new_note flex justify-end my-2">
      <button type="button" @click="opennewnoteModal" class="btn btn-default btn-primary">{{__('Add a note')}}</button>
    </div><!-- add_new_note -->

    <!-- Filters Area -->
    <div class="content_page">
        <div class="filter_area">
            <div class="item w-1/4">
                <date-filter :enable-seconds="false" v-model="date" :enable-time="false" :date-format="'Y-m-d'" :twelve-hour-time="false" :locale="locale" :placeholder="__('Note Date')" />
            </div><!-- item -->
            <div class="item">
                <select v-model="user_id" @change="getDays">
                    <option :value="null" :selected="true">{{__('User')}}</option>
                    <option v-for="(user,i) in users" :key="i"  :value="user.id">{{user.name}}</option>

                </select>
            </div><!-- item -->
            <div class="reset_filters">
                <button
                    @click="resetFilters"
                    v-tooltip="{
                        targetClasses: ['it-has-a-tooltip'],
                        placement: 'top',
                        content: __('Reset Filters'),
                        classes: ['tooltip_reset']
                        }"
                >
                </button>
            </div><!-- reset_filters -->
        </div>
    </div>


    <div id="all_notes" v-if="days.length" class="relative">
        <loading :active.sync="isLoading" transition="fade" :blur="'2px'" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.8" :is-full-page="false"></loading>

    <!-- <div class="w-full flex flex-wrap mt-3 justify-center">
        <pagination
            v-if="paginator.lastPage > 1"
            :page-count="paginator.lastPage"
            :page-range="3"
            :margin-pages="2"
            :click-handler="getDays"
            :value="paginator.currentPage"
            :prev-text="__('Previous')"
            :next-text="__('Next')"
            :container-class="'pagination  w-full flex justify-center'"
            :page-class="'page-item'"
            :page-link-class="'page-link'"
            :prev-link-class="'page-link'"
            :next-link-class="'page-link'"
            :prev-class="'page-item'"
            :next-class="'page-item'"
            :first-last-button="true"
            :first-button-text="__('First')"
            :last-button-text="__('Last')"
        />
    </div>
    <div class="Results_area" v-if="days.length">
        <p>{{__('Results')}}  : {{__('From')}} ( {{paginator.from}} ) - {{__('To')}}  ( {{paginator.to}} )</p>
        <p>{{__('Count')}}  : {{paginator.totalResults}}</p>
    </div> -->

    <div v-for="(obj,i) in days" :key="i">

        <!-- Draw Headers Dates  -->
         <div class="today w-full px-3 relative">
            <span>
                {{ getDay(obj.day) }}
            </span>
            <span class="px-1">
                {{ convertDate(obj.day) }}
            </span>

            <span class="px-2"> - {{__('Notes Count')}} ({{obj.notes.length}})</span>
        </div>
        <div class="flex flex-wrap -mx-3 relative" v-if="obj.notes.length">

                <div class="w-full sm:w-1/3 md:w-1/3 lg:w-1/4 xl:w-1/4 px-3"  v-for="(note,i) in obj.notes" :key="i">
                    <div class="note_item">
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26.199" height="29.197" viewBox="0 0 26.199 29.197"><g transform="translate(-40.42 -7.854)"><g transform="translate(41.383 8.833)"><path d="M191.695,101.029l-.041-.029-7.454,8.548,6.3,4.874,6.325-9.431-4.113-3.171Z" transform="translate(-176.751 -96.53)" fill="#ee4e71"/><path d="M319.3,30.435l-2.832-2.194-4.429-3.423a1.085,1.085,0,0,0-1.749.9l.123,3.376,1.018.784,2.364,1.825,1.784,1.381,3.3-.731A1.086,1.086,0,0,0,319.3,30.435Z" transform="translate(-295.46 -24.59)" fill="#ee4e71"/><path d="M70.237,256.74a7.571,7.571,0,0,0,.386-7.582l-6.3-4.868a7.589,7.589,0,0,0-7.243,2.282.759.759,0,0,0,.094,1.112l5.529,4.277,6.424,4.967A.767.767,0,0,0,70.237,256.74Z" transform="translate(-56.885 -231.266)" fill="#ee4e71"/></g><path d="M90.026,375.667l3.926-4.446-1.562-1.205-.667-.515-.351.515-2.972,4.4Z" transform="translate(-45.173 -340.487)" fill="#d6effb"/><path d="M65.817,13.906l-2.832-2.194L58.556,8.29a2.061,2.061,0,0,0-3.323,1.7l.105,2.919-6.874,7.881a8.506,8.506,0,0,0-7.582,2.685,1.73,1.73,0,0,0,.211,2.545l3.271,2.527h0a2.428,2.428,0,0,1,.374.269,1.017,1.017,0,0,1,.357.486c.059.234-.111.415-.257.579a.591.591,0,0,0-.076.094c-.012.012-.053.064-.059.07a.893.893,0,0,0-.064.094c-.088.129-.176.257-.275.38h0l-1.351,2.007-.351-.275A.976.976,0,0,0,41.466,33.8l1.176.907h0l1.621,1.252h0l1.153.889a.983.983,0,0,0,.6.2.966.966,0,0,0,.772-.38.978.978,0,0,0-.176-1.369l-.333-.257,2.633-2.984,4.136,3.2a1.1,1.1,0,0,0,.135.094.118.118,0,0,0,.047.023.818.818,0,0,0,.1.053c.018.006.035.018.053.023.035.018.07.029.105.047l.047.018a1.614,1.614,0,0,0,.158.047.021.021,0,0,1,.018.006c.047.012.094.018.14.029.018,0,.035.006.053.006.035.006.076.006.111.012H54.1a.334.334,0,0,0,.076-.006h.053l.164-.018c.059-.012.117-.023.176-.041a1.739,1.739,0,0,0,.983-.731c.059-.094.117-.193.176-.287.018-.029.035-.064.053-.094a2.283,2.283,0,0,0,.111-.2c.018-.035.041-.07.059-.105.035-.064.07-.135.1-.2.018-.035.035-.07.053-.1.041-.082.076-.158.111-.24a.273.273,0,0,1,.029-.059l.123-.3a.459.459,0,0,0,.029-.082c.029-.076.053-.146.082-.222l.035-.105c.023-.07.047-.135.064-.2l.035-.105c.023-.076.041-.152.064-.228.006-.029.018-.059.023-.082.023-.105.047-.211.07-.31.006-.018.006-.035.012-.053.018-.088.035-.176.047-.263l.018-.105c.012-.07.023-.146.029-.216a.717.717,0,0,0,.012-.111c.006-.076.018-.152.023-.228a.568.568,0,0,0,.006-.1c.006-.105.012-.211.018-.322h.012v-.012c-.018-.345-.07-.685-.088-1.03a4.465,4.465,0,0,0-.094-.924.2.2,0,0,1-.006-.082,8.6,8.6,0,0,0-.509-1.638l5.769-8.6L65,17.551a2.06,2.06,0,0,0,.813-3.645ZM44.725,33.851l-.176-.135L46.72,30.5h.164l.474.369Zm10.2-2.7a6.551,6.551,0,0,1-.878,2.422L48.4,29.206l-1.252-.971h-.006l-4.669-3.61a6.592,6.592,0,0,1,5.933-1.872l5.956,4.6a6.692,6.692,0,0,1,.6,2.247c.006.076.012.152.018.234A6.482,6.482,0,0,1,54.923,31.148Zm-.018-5.827-4.657-3.6,6.178-7.079,3.241,2.5.48.369Zm9.759-9.759a.107.107,0,0,1-.088.088l-2.849.632-4.441-3.434-.105-2.919a.107.107,0,0,1,.064-.105.1.1,0,0,1,.123.012l7.261,5.617A.1.1,0,0,1,64.664,15.562Z" transform="translate(0 0)"/></g></svg>
                    </div><!-- icon -->
                    <pre>{{note.body}}</pre>
                    <div class="foot flex justify-between">
                        <div>
                        <span>{{__('Added By')}} : {{note.user.name}}</span>
                        <time>{{note.time_created}}</time>
                        </div>
                        <div class="self-end">

                        <button v-if="current_user_id === note.user.id" @click="opennEditnoteModal(note)" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="16.506" height="16.507" viewBox="0 0 16.506 16.507"><path d="M5.551,10.509,13.81,2.25a.826.826,0,0,1,1.156,0l3.3,3.3a.826.826,0,0,1,0,1.156l-8.259,8.259a.826.826,0,0,1-.578.248H6.13a.826.826,0,0,1-.826-.826v-3.3a.826.826,0,0,1,.248-.578Zm1.4,3.056H9.094l7.433-7.433L14.389,3.993,6.955,11.426Zm8.259-1.652a.826.826,0,1,1,1.652,0v4.955a1.652,1.652,0,0,1-1.652,1.652H3.652A1.652,1.652,0,0,1,2,16.869V5.306A1.657,1.657,0,0,1,3.652,3.654H8.607a.826.826,0,0,1,0,1.652H3.652V16.869H15.214Z" transform="translate(-2 -2.014)" fill="#b3b9bf"/></svg></button>
                        <button v-if="current_user_id === note.user.id || currentUserIsAdmin" @click="deleteNote(note)" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="16.518" height="16.518" viewBox="0 0 16.518 16.518"><path d="M6.955,5.3V3.652A1.657,1.657,0,0,1,8.607,2h3.3a1.652,1.652,0,0,1,1.652,1.652V5.3h4.13a.826.826,0,0,1,0,1.652h-.826v9.911a1.652,1.652,0,0,1-1.652,1.652H5.3a1.652,1.652,0,0,1-1.652-1.652V6.955H2.826a.826.826,0,0,1,0-1.652ZM5.3,6.955v9.911h9.911V6.955ZM11.911,5.3V3.652h-3.3V5.3Zm-3.3,3.3a.826.826,0,0,1,.826.826v4.955a.826.826,0,1,1-1.652,0V9.433A.826.826,0,0,1,8.607,8.607Zm3.3,0a.826.826,0,0,1,.826.826v4.955a.826.826,0,1,1-1.652,0V9.433A.826.826,0,0,1,11.911,8.607Z" transform="translate(-2 -2)" fill="#b3b9bf"/></svg></button>
                        </div>
                    </div><!-- foot -->
                    </div><!-- note_item -->
                </div><!-- w-full -->

        </div>
        <div class="w-full text-center" v-else>
            <div class="message current">
                {{__('No notes found in this day')}}
            </div>
        </div>

    </div>


    <!-- Pagination -->
    <div class="w-full flex flex-wrap mt-3 justify-center">
        <pagination
            v-if="paginator.lastPage > 1"
            :page-count="paginator.lastPage"
            :page-range="3"
            :margin-pages="2"
            :click-handler="getDays"
            :value="paginator.currentPage"
            :prev-text="__('Previous')"
            :next-text="__('Next')"
            :container-class="'pagination  w-full flex justify-center'"
            :page-class="'page-item'"
            :page-link-class="'page-link'"
            :prev-link-class="'page-link'"
            :next-link-class="'page-link'"
            :prev-class="'page-item'"
            :next-class="'page-item'"
            :first-last-button="true"
            :first-button-text="__('First')"
            :last-button-text="__('Last')"
        />
    </div><!-- Pagination -->
    <div class="Results_area" v-if="days.length">
        <p>{{__('Results')}}  : {{__('From')}} ( {{paginator.from}} ) - {{__('To')}}  ( {{paginator.to}} )</p>
        <p>{{__('Count')}}  : {{paginator.totalResults}}</p>
    </div><!-- Results_area -->

    </div><!-- all_notes -->
    <div class="w-full text-center" v-else>
        <div class="message">
            {{__('No notes found !!!')}}
        </div>
    </div>



    <!-- <infinite-loading :identifier="infiniteId" @infinite="getUnits" spinner="spiral">
      <div slot="no-more" class="notes_are_over"></div>
      <div slot="no-results" class="no_notes">{{__('There are no Notes')}}</div>
      <div slot="error" class="error_appeared">{{__('An error appeared, please try again')}}</div>
    </infinite-loading> -->

    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="true" overlay-theme="dark" ref="newnoteModal">
      <div id="new_note_modal">
        <div class="title">
          <svg xmlns="http://www.w3.org/2000/svg" width="23.2" height="26.889" viewBox="0 0 23.2 26.889"><g transform="translate(-34.5)"><g transform="translate(34.729 0.272)"><path d="M50.177,75.129h2.729A2.642,2.642,0,0,0,51.088,72.4h3.641a1.824,1.824,0,0,1,1.818,1.818V93.324a1.824,1.824,0,0,1-1.818,1.818H40.171V90.136a1.341,1.341,0,0,0-1.365-1.365H33.8V74.218A1.824,1.824,0,0,1,35.618,72.4H49.265a2.642,2.642,0,0,0-1.818,2.729Z" transform="translate(-33.8 -68.759)" fill="#fdfb8d"/><path d="M295.259,10.471H289.8a2.642,2.642,0,0,1,1.818-2.729V5.923s-.912,0-.912-.453V4.1h3.641V5.465c0,.453-.912.453-.912.453V7.736a2.654,2.654,0,0,1,1.823,2.735" transform="translate(-276.153 -4.1)" fill="#ff7474"/><path d="M40.171,380.965v5.006L33.8,379.6h5.006a1.341,1.341,0,0,1,1.365,1.365" transform="translate(-33.8 -359.582)" fill="#aab1ba"/></g><path d="M41.1,26.884c-.048,0-.139-.048-.181-.048l-6.371-6.365c-.048-.048-.048-.091-.048-.181V5.731a2.041,2.041,0,0,1,2.047-2.047H47.012a.229.229,0,1,1,0,.458H36.552a1.616,1.616,0,0,0-1.594,1.594V20.2l5.912,5.907V21.654a1.126,1.126,0,0,0-1.135-1.135H37.459a.229.229,0,1,1,0-.458h2.276a1.616,1.616,0,0,1,1.594,1.594V26.66a.259.259,0,0,1-.139.229A.548.548,0,0,0,41.1,26.884Zm14.559,0H42.918a.229.229,0,1,1,0-.458H55.653a1.616,1.616,0,0,0,1.594-1.594V5.731a1.616,1.616,0,0,0-1.594-1.594H55.2a.229.229,0,1,1,0-.458h.453A2.041,2.041,0,0,1,57.7,5.725V24.832A2.031,2.031,0,0,1,55.659,26.884ZM52.018,18.7H45.647a.229.229,0,1,1,0-.458h6.371a.229.229,0,1,1,0,.458Zm-.912-2.729H46.559a.229.229,0,0,1,0-.458h4.547a.229.229,0,1,1,0,.458Zm-6.365,0H39.282a.229.229,0,0,1,0-.458h5.459a.215.215,0,0,1,.229.229A.218.218,0,0,1,44.741,15.966Zm8.188-2.729H50.2a.229.229,0,1,1,0-.458h2.729a.215.215,0,0,1,.229.229A.218.218,0,0,1,52.929,13.237Zm-4.553,0H42.918a.229.229,0,1,1,0-.458h5.459a.229.229,0,0,1,0,.458Zm-7.277,0H39.282a.229.229,0,0,1,0-.458H41.1a.229.229,0,0,1,0,.458Zm5.459-2.729H39.282a.229.229,0,0,1,0-.458h7.277a.229.229,0,1,1,0,.458ZM51.106,9.6a.215.215,0,0,1-.229-.229V6.642a.215.215,0,0,1,.229-.229h2.506a2.451,2.451,0,0,0-1.637-2.276.309.309,0,0,1-.181-.229V2.1a.215.215,0,0,1,.229-.229c.272,0,.682-.091.682-.229V.5H49.523V1.637c0,.139.41.229.682.229a.215.215,0,0,1,.229.229V3.913a.309.309,0,0,1-.181.229,2.333,2.333,0,0,0-1.637,2.276H49.3a.229.229,0,0,1,0,.458h-.912a.215.215,0,0,1-.229-.229,2.963,2.963,0,0,1,1.818-2.911V2.276c-.453-.048-.912-.229-.912-.682V.229A.215.215,0,0,1,49.293,0h3.641a.215.215,0,0,1,.229.229V1.594c0,.41-.453.634-.912.682V3.732A2.908,2.908,0,0,1,54.07,6.642a.215.215,0,0,1-.229.229h-2.5v2.5A.226.226,0,0,1,51.106,9.6Z" fill="#51565f"/></g></svg>{{__('Staff feedback')}}
        </div><!-- title -->
        <div class="content_area">
          <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="26.199" height="29.197" viewBox="0 0 26.199 29.197"><g transform="translate(-40.42 -7.854)"><g transform="translate(41.383 8.833)"><path d="M191.695,101.029l-.041-.029-7.454,8.548,6.3,4.874,6.325-9.431-4.113-3.171Z" transform="translate(-176.751 -96.53)" fill="#ee4e71"/><path d="M319.3,30.435l-2.832-2.194-4.429-3.423a1.085,1.085,0,0,0-1.749.9l.123,3.376,1.018.784,2.364,1.825,1.784,1.381,3.3-.731A1.086,1.086,0,0,0,319.3,30.435Z" transform="translate(-295.46 -24.59)" fill="#ee4e71"/><path d="M70.237,256.74a7.571,7.571,0,0,0,.386-7.582l-6.3-4.868a7.589,7.589,0,0,0-7.243,2.282.759.759,0,0,0,.094,1.112l5.529,4.277,6.424,4.967A.767.767,0,0,0,70.237,256.74Z" transform="translate(-56.885 -231.266)" fill="#ee4e71"/></g><path d="M90.026,375.667l3.926-4.446-1.562-1.205-.667-.515-.351.515-2.972,4.4Z" transform="translate(-45.173 -340.487)" fill="#d6effb"/><path d="M65.817,13.906l-2.832-2.194L58.556,8.29a2.061,2.061,0,0,0-3.323,1.7l.105,2.919-6.874,7.881a8.506,8.506,0,0,0-7.582,2.685,1.73,1.73,0,0,0,.211,2.545l3.271,2.527h0a2.428,2.428,0,0,1,.374.269,1.017,1.017,0,0,1,.357.486c.059.234-.111.415-.257.579a.591.591,0,0,0-.076.094c-.012.012-.053.064-.059.07a.893.893,0,0,0-.064.094c-.088.129-.176.257-.275.38h0l-1.351,2.007-.351-.275A.976.976,0,0,0,41.466,33.8l1.176.907h0l1.621,1.252h0l1.153.889a.983.983,0,0,0,.6.2.966.966,0,0,0,.772-.38.978.978,0,0,0-.176-1.369l-.333-.257,2.633-2.984,4.136,3.2a1.1,1.1,0,0,0,.135.094.118.118,0,0,0,.047.023.818.818,0,0,0,.1.053c.018.006.035.018.053.023.035.018.07.029.105.047l.047.018a1.614,1.614,0,0,0,.158.047.021.021,0,0,1,.018.006c.047.012.094.018.14.029.018,0,.035.006.053.006.035.006.076.006.111.012H54.1a.334.334,0,0,0,.076-.006h.053l.164-.018c.059-.012.117-.023.176-.041a1.739,1.739,0,0,0,.983-.731c.059-.094.117-.193.176-.287.018-.029.035-.064.053-.094a2.283,2.283,0,0,0,.111-.2c.018-.035.041-.07.059-.105.035-.064.07-.135.1-.2.018-.035.035-.07.053-.1.041-.082.076-.158.111-.24a.273.273,0,0,1,.029-.059l.123-.3a.459.459,0,0,0,.029-.082c.029-.076.053-.146.082-.222l.035-.105c.023-.07.047-.135.064-.2l.035-.105c.023-.076.041-.152.064-.228.006-.029.018-.059.023-.082.023-.105.047-.211.07-.31.006-.018.006-.035.012-.053.018-.088.035-.176.047-.263l.018-.105c.012-.07.023-.146.029-.216a.717.717,0,0,0,.012-.111c.006-.076.018-.152.023-.228a.568.568,0,0,0,.006-.1c.006-.105.012-.211.018-.322h.012v-.012c-.018-.345-.07-.685-.088-1.03a4.465,4.465,0,0,0-.094-.924.2.2,0,0,1-.006-.082,8.6,8.6,0,0,0-.509-1.638l5.769-8.6L65,17.551a2.06,2.06,0,0,0,.813-3.645ZM44.725,33.851l-.176-.135L46.72,30.5h.164l.474.369Zm10.2-2.7a6.551,6.551,0,0,1-.878,2.422L48.4,29.206l-1.252-.971h-.006l-4.669-3.61a6.592,6.592,0,0,1,5.933-1.872l5.956,4.6a6.692,6.692,0,0,1,.6,2.247c.006.076.012.152.018.234A6.482,6.482,0,0,1,54.923,31.148Zm-.018-5.827-4.657-3.6,6.178-7.079,3.241,2.5.48.369Zm9.759-9.759a.107.107,0,0,1-.088.088l-2.849.632-4.441-3.434-.105-2.919a.107.107,0,0,1,.064-.105.1.1,0,0,1,.123.012l7.261,5.617A.1.1,0,0,1,64.664,15.562Z" transform="translate(0 0)"/></g></svg>
          </div><!-- icon -->
          <textarea v-model="body" name="" id="" cols="30" rows="10"></textarea>
          <div style="text-align: left;">
            <button v-if="createState" type="button" class="shadow mb-2  btn btn-block btn-primary mt-2" @click="addNote">{{__('Save')}}</button>
            <button v-if="!createState" type="button" class="shadow mb-2  btn btn-block btn-primary mt-2" @click="editNote">{{__('Edit')}}</button>
          </div>
        </div><!-- content_area -->
      </div><!-- new_note_modal -->
    </sweet-modal>

    <delete-note-confirm :id="target_note_id" ref="deleteNote" />

 	</div><!-- Notes_page -->
</template>

<script>
import Loading from 'vue-loading-overlay';
import DeleteNoteConfirm from './DeleteNoteConfirm';
import DateFilter from './DateFilter';
import Pagination from './Pagination';
import momenttimezone from 'moment-timezone'
import moment from 'moment'
    export default {
        components: {
            Pagination,
            Loading,
            DateFilter,
            DeleteNoteConfirm
        },
        props: {
            textAlign : {
                type : String ,
                default : "text-left"
            }
        },
        data: () => {
            return {
                loading: false,
                days: [],
                body: null,
                createState: true,
                selectedNoteId: null,
                infiniteId: +new Date(),
                paginator : {},
                isLoading : false,
                locale : null,
                date: null,
                target_note_id : null,
                current_user_id : Nova.app.user.id,
                currentUserIsAdmin : false,
                user_id : null,
                users : []
            }
        },
        created(){
            this.checkCurrentUserIsAdmin();
        },
        mounted() {
            let lang = Nova.config.local ;
            lang == 'en' ? this.textAlign = 'text-left' : this.textAlign = 'text-right' ;
            this.locale = Nova.config.local
            Nova.$on('selected_date' , (val) => {
                this.date = val ;
                this.getDays();
            });

            Nova.$on('note-deleted' , () => {
                this.getDays();
            })

            this.getDays();
        },
        methods: {
            checkCurrentUserIsAdmin(){
                axios.get(`/apidata/user-is-admin?id=${this.current_user_id}`)
                .then(response => {
                    this.currentUserIsAdmin = response.data.is_admin;
                    this.users = response.data.users;
                })
            },
            resetFilters(){
                this.date = null;
                this.user_id = null;
                this.getDays();
            },
            getDays(page = 1){
                this.isLoading = true;
                let url = `/apidata/notes?page=${page}`;

                if(this.date){
                    url += `&filter_by_date=${this.date}`;
                }

                if(this.user_id) {
                    url += `&filter_by_user=${this.user_id}`;
                }

                axios.get(url)
                    .then(response => {
                        this.days = response.data.data;
                        this.paginator = {
                            currentPage : response.data.meta.current_page ,
                            lastPage : response.data.meta.last_page ,
                            from : response.data.meta.from,
                            to : response.data.meta.to,
                            totalResults : response.data.meta.total,
                            pathPage : response.data.meta.path + '?page=',
                            firstPageUrl : response.data.links.first ,
                            lastPageUrl : response.data.links.last ,
                            nextPageUrl : response.data.links.next ,
                            prevPageUrl : response.data.links.prev ,
                        };
                        this.isLoading = false
                    })
            },
        //   async getUnits(state){
        //     await axios.get('/apidata/notes',{
        //       params:{
        //         page: this.page
        //       }
        //     })
        //     .then((res) => {
        //       if (res.data.data.length) {
        //         this.page += 1;
        //         this.notes.push(...res.data.data);
        //         state.loaded();
        //       } else {
        //         state.complete();
        //       }
        //       this.$refs.newnoteModal.close()
        //     }).catch(error => {
        //         state.error()
        //     })
        //   },

          addNote() {
            if(!this.body){
                 this.$toasted.error(this.__('Note Body Required'), {
                      duration: 3000
                  });
                  return false;
            }
            axios.post('/apidata/notes', {
              body: this.body
            }).then(res => {


              this.$refs.newnoteModal.close()
              this.body = null
              this.$toasted.success(this.__('Note added successfully'), {
                 duration: 3000
              });
              this.getDays();

            })
          },

          editNote() {
            if(!this.body){
                 this.$toasted.error(this.__('Note Body Required'), {
                      duration: 3000
                  });
                  return false;
            }
            axios.put(`/apidata/notes/${this.selectedNoteId}`, {
              body: this.body
            }).then(res => {
                this.$toasted.success(this.__('Note edited successfully'), {
                    duration: 3000
                })
                this.$refs.newnoteModal.close()
                this.body = null;

                this.getDays();


            })
          },

          deleteNote(note) {

              // pre check here for an admin trying to delete other admins note
              if(this.currentUserIsAdmin){

                  if(this.current_user_id != note.user.id && note.user_is_admin){
                    this.$toasted.error(this.__('Sorry , but you can not delete other admin note'), {
                        duration: 3000
                    });
                    return;
                  }

              }

              this.target_note_id = note.id;
              setTimeout(() => {
                  this.$refs.deleteNote.$refs.deleteNoteConfirm.open();
              }, 0);

          },

          getDay(value) {
            return moment(value).lang(Nova.config.local).format('dddd')
          },

          convertDate(value) {
            return moment(value).lang('en').format('DD /MM/ YYYY')
          },

          convertTime(value) {
            return momenttimezone(value).tz("Asia/Riyadh").format("HH:mm a");
          },

          opennewnoteModal() {
            this.selectedNoteId = null
            this.body = null
            this.createState = true
            this.$refs.newnoteModal.open()
          },

          opennEditnoteModal(note) {
            this.selectedNoteId = note.id
            this.body = note.body
            this.createState = false
            this.$refs.newnoteModal.open()
          },
        }

    }
</script>

<style scoped>


.message{
    color: #6b798e;
    font-size: 20px;
}

.current {
    font-size: 15px;;
}

#all_notes .today {
  font-size: 20px;
  margin-top: 40px;
}
#all_notes .today:first-of-type {margin: 0 auto;}
#all_notes .note_item {
  margin: 35px auto 20px;
  background: #FDFB8D;
  border-radius: 10px;
  border: 1px solid #E3E15C;
  padding: 5px;
  position: relative;
  min-height: 293px;
}
#all_notes .note_item .icon {
  position: absolute;
  margin: 0 auto;
  display: table;
  top: -15px;
  right: 0;
}
html:lang(en) #all_notes .note_item .icon {
  left: 0;
  right: auto;
}
html:lang(en) #all_notes .note_item .icon {
  -webkit-transform: rotatey(180deg);
  transform: rotatey(180deg);
}
#all_notes .note_item pre {
  overflow-y: auto;
  height: 208px;
  font-size: 16px;
  margin: 15px auto;
  color: #333B45;
  padding: 5px;
  font-family: "Dubai-Regular";
  white-space: pre-line;
}
#all_notes .note_item .foot span {
  display: block;
  font-size: 14px;
  color: #777777;
}
#all_notes .note_item .foot time {
  display: block;
  direction: ltr;
  text-align: right;
  font-size: 14px;
  color: #777777;
}
html:lang(en) #all_notes .note_item .foot time {text-align: left;}
#all_notes .note_item .foot button {
  display: inline-block;
  margin: 5px;
}
#all_notes .note_item .foot button svg {
  width: 23px;
  height: 23px;
}
#new_note_modal .title {
  text-align: initial;
  font-size: 20px;
  border-bottom: 1px solid #ddd;
  padding: 0 0 10px;
  height: 55px;
  line-height: 45px;
}
#new_note_modal .title svg {
  float: right;
  margin: 8px 0 0 10px;
}
html:lang(en) #new_note_modal .title svg {
  float: left;
  margin: 8px 10px 0 0;
}
#new_note_modal .content_area {
  margin: 30px auto 0;
  position: relative;
}
#new_note_modal .content_area .icon {
  overflow: auto;
  position: absolute;
  margin: 0 auto;
  display: table;
  top: -15px;
  right: 0;
}
html:lang(en) #new_note_modal .content_area .icon {
  left: 0;
  right: auto;
}
html:lang(en) #new_note_modal .content_area .icon {
  -webkit-transform: rotatey(180deg);
  transform: rotatey(180deg);
}
#new_note_modal .content_area textarea {
  background: #FDFB8D;
  width: 100%;
  border: 1px solid #E3E15C;
  border-radius: 5px;
  padding: 25px 10px 10px 10px;
  color: #000;
  font-size: 16px;
  margin: 0 auto;
}
#new_note_modal .content_area button {
  background: #EE4E71;
  -webkit-box-shadow: none;
  box-shadow: none;
  border: none;
  display: inline-block;
  width: auto;
  padding: 0 20px;
  min-width: 25%;
  max-width: 100%;
  font-size: 17px;
  height: 40px;
  line-height: 40px;
}
#new_note_modal .content_area button:hover {background: #DF3F62;}
</style>

<style lang="scss" scoped>
 .content_page {
            /* background: #fff; */
            padding: 10px;
            .filter_area {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                justify-content: flex-start;
                margin: 0 -10px;
                .item {
                    width: 20%;
                    padding: 0 10px;
                    margin: 0 0 10px;
                    @media (min-width: 320px) and (max-width: 480px) {
                        width: 50%;
                    } /* media */
                    @media (min-width: 481px) and (max-width: 767px) {
                        width: 33.33333%;
                    } /* media */
                    @media (min-width: 768px) and (max-width: 991px) {
                        width: 25%;
                    } /* media */
                    input {
                        background: #fafafa;
                        height: 40px;
                        padding: 0 10px;
                        font-size: 15px;
                        border: 1px solid #ddd !important;
                        color: #000;
                        width: 100%;
                        border-radius: 4px !important;
                        outline: none;
                    } /* input */
                    select {
                        background-color: #fafafa;
                        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Layer_1' x='0px' y='0px' viewBox='0 0 512.011 512.011' style='enable-background:new 0 0 512.011 512.011;' xml:space='preserve' width='512px' height='512px' class=''%3E%3Cg%3E%3Cg%3E%3Cg%3E%3Cpath d='M505.755,123.592c-8.341-8.341-21.824-8.341-30.165,0L256.005,343.176L36.421,123.592c-8.341-8.341-21.824-8.341-30.165,0 s-8.341,21.824,0,30.165l234.667,234.667c4.16,4.16,9.621,6.251,15.083,6.251c5.462,0,10.923-2.091,15.083-6.251l234.667-234.667 C514.096,145.416,514.096,131.933,505.755,123.592z' data-original='%23000000' class='active-path' fill='%23000000'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
                        background-repeat: no-repeat;
                        background-size: 14px;
                        background-position: 10px center;
                        height: 40px;
                        padding: 0 10px;
                        font-size: 15px;
                        border: 1px solid #ddd !important;
                        color: #000;
                        width: 100%;
                        border-radius: 4px !important;
                        outline: none;
                        -webkit-appearance: none;
                        -moz-appearance: none;
                        -o-appearance: none;
                        appearance: none;
                    } /* select */
                } /* item */
                .reset_filters {
                    width: 100%;
                    display: flex;
                    padding: 0 10px;
                    justify-content: flex-end;
                    button {
                        height: 40px;
                        width: 40px;
                        background-color: #718096;
                        border-radius: 4px;
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16.866' height='18.447' viewBox='0 0 16.866 18.447'%3E%3Cg transform='translate(0 0)'%3E%3Cpath d='M24.417,3.658a7.354,7.354,0,0,1,9.56-.252l-2.189.083a.509.509,0,0,0,.019,1.017h.019l3.36-.124a.508.508,0,0,0,.49-.509v-.06h0L35.552.49a.509.509,0,1,0-1.017.038l.079,2.083A8.364,8.364,0,0,0,23.735,2.9a8.367,8.367,0,0,0-2.516,8.178.506.506,0,0,0,.493.388.441.441,0,0,0,.121-.015.509.509,0,0,0,.373-.614A7.349,7.349,0,0,1,24.417,3.658Z' transform='translate(-20.982 0)' fill='%23ffffff'/%3E%3Cpath d='M91.8,185.6a.508.508,0,1,0-.987.241,7.348,7.348,0,0,1-11.832,7.387l2.215-.2a.509.509,0,1,0-.094-1.013l-3.349.3a.508.508,0,0,0-.46.554l.3,3.349a.508.508,0,0,0,.5.463.183.183,0,0,0,.045,0,.508.508,0,0,0,.46-.554l-.181-2.038a8.308,8.308,0,0,0,4.833,1.842c.143.008.286.011.426.011A8.365,8.365,0,0,0,91.8,185.6Z' transform='translate(-75.175 -178.237)' fill='%23ffffff'/%3E%3C/g%3E%3C/svg%3E");
                        background-repeat: no-repeat;
                        background-position: center center;
                        background-size: 20px;
                        -webkit-transition: all 0.2s ease-in-out;
                        -moz-transition: all 0.2s ease-in-out;
                        -o-transition: all 0.2s ease-in-out;
                        transition: all 0.2s ease-in-out;
                        &:hover {
                            background-color: #5E6D83;
                        } /* hover */
                    } /* button */
                } /* reset_filters */
            } /* filter_area */
            hr {
                margin: 20px auto;
                border-color: #ddd;
                &:last-of-type {
                    margin: 0 0 20px;
                } /* last-of-type */
            } /* hr */
            .statistics_area {
                ul {
                    display: flex;
                    align-items: flex-start;
                    justify-content: flex-start;
                    flex-wrap: wrap;
                    margin: 0 -10px;
                    li {
                        width: 20%;
                        padding: 0 10px;
                        margin: 0 0 20px;
                        @media (min-width: 320px) and (max-width: 480px) {
                            width: 50%;
                        } /* media */
                        @media (min-width: 481px) and (max-width: 767px) {
                            width: 33.33333%;
                        } /* media */
                        @media (min-width: 768px) and (max-width: 991px) {
                            width: 25%;
                        } /* media */
                        span {
                            display: block;
                            font-size: 15px;
                            color: #000;
                            margin: 0 0 5px;
                        } /* span */
                        p {
                            display: block;
                            font-size: 16px;
                            font-weight: bold;
                            &.totalDebtor {
                                color: #f56565;
                            } /* totalDebtor */
                            &.totalCreditor {
                                color: #48bb78;
                            } /* totalCreditor */
                        } /* p */
                    } /* li */
                } /* ul */
            } /* statistics_area */
            .action_buttons {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                margin: 0 auto 10px;
                button {
                    display: block;
                    height: 30px;
                    width: 30px;
                    margin: 0 10px 0 0;
                    outline: none;
                    background-position: center center;
                    background-size: 25px;
                    background-repeat: no-repeat;
                    &.excel_button {
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='23.308' height='23.308' viewBox='0 0 23.308 23.308'%3E%3Cpath d='M24.213,3H16V5.675h2.717V7.5H16V9.275h2.689v1.793H16v1.793h2.689v1.793H16v1.793h2.689V18.24H16v2.689h8.213a.768.768,0,0,0,.751-.78V3.78A.768.768,0,0,0,24.213,3ZM23.172,18.24H19.586V16.447h3.586Zm0-3.586H19.586V12.861h3.586Zm0-3.586H19.586V9.275h3.586Zm0-3.586H19.586V5.689h3.586Z' transform='translate(-1.657 -0.311)' fill='%23333b45'/%3E%3Cpath d='M0,2.59V20.719l13.447,2.589V0ZM8.505,16.208,6.941,13.25a2.623,2.623,0,0,1-.184-.608H6.733a4.6,4.6,0,0,1-.21.634l-1.57,2.931H2.516l2.894-4.54L2.763,7.128H5.251l1.3,2.723a4.756,4.756,0,0,1,.273.766h.025q.077-.266.285-.792l1.443-2.7h2.279l-2.723,4.5,2.8,4.578Z' fill='%23333b45'/%3E%3C/svg%3E");
                    } /* excel_button */
                    &.print_button {
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23333b45'/%3E%3C/svg%3E");
                    } /* print_button */
                } /* button */
            } /* action_buttons */
            .table_area {
                .no_data_show {
                    text-align: center;
                    padding: 50px 15px 40px;
                    svg {
                        display: block;
                        margin: 0 auto 15px;
                    } /* svg */
                    span {
                        display: block;
                        font-size: 15px;
                        text-align: center;
                        color: #000;
                    } /* span */
                } /* no_data_show */
            } /* table_area */
        } /* content_page */


.Results_area {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    p {
        display: block;
        margin: 10px 0 0;
        font-size: 15px;
        color: #000;
    } /* p */
} /* Results_area */
</style>
