<template>
    <div>
        <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Add New Comment')" overlay-theme="dark" ref="commentModal" class="cancel_reservation_modal ticket_modal_crud">
            <div class="ticket_create relative">
                <loading :active="isLoading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7" :height="20" :width="20" :is-full-page="false"></loading>
                <div class="row_group">
                    <div class="col">
                        <textarea wrap="soft" :placeholder="__('Write your comment here ..')" rows="10" v-model="comment" style="width:100%;"></textarea>
                    </div><!-- ticket type col -->
                </div><!-- row_group -->
            </div>
        <div class="action_buttons">
            <button class="table w-full" @click="addComment()">{{__('Add a comment')}}</button>
        </div><!-- action_buttons -->
    </sweet-modal>
          <div class="row_cols">

              <nav v-if="crumbs.length" class="mb-5">
                <ul class="breadcrumbs">
                    <li class="breadcrumbs__item" v-for="(crumb,i) in crumbs" :key="i">
                        <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
                    </li>
                </ul>
            </nav>

        <div class="right_col">



          <div class="booking_summary" v-if="ticket">
            <div class="title">
              <span>{{__('Ticket details')}}</span>
            </div><!-- title -->
            <div class="content">
              <ul>
                <li class="oneline">
                  <div class="name">{{__('Ticket Number')}} :</div>
                  <div class="col_two">
                    <div class="desc" >
                      <span style="direction: ltr;"> #{{ticket.id}} </span>
                    </div>
                  </div><!-- col_two -->
                </li>
                <li class="oneline">
                  <div class="name">{{__('Subject')}} :</div>
                  <div class="col_two">
                    <div class="desc" >
                      <span style="direction: ltr;"> {{ticket.title}} </span>
                    </div>
                  </div><!-- col_two -->
                </li>
                <li class="oneline">
                  <div class="name">{{__('Description')}} :</div>
                  <div class="col_two">
                    <div class="desc" >
                      <span style="direction: ltr;"> {{ticket.body}} </span>
                    </div>
                  </div><!-- col_two -->
                </li>
                <li class="oneline">
                  <div class="name">{{__('Date')}} :</div>
                  <div class="col_two">
                    <div class="desc" >
                      <span style="direction: ltr;"> {{ticket.created_at}} </span>
                    </div>
                  </div><!-- col_two -->
                </li>
                <li class="oneline" v-if="ticket.attachments.length > 0">
                  <div class="name">{{__('Attachment')}} :</div>
                  <div class="col_two">
                    <div class="desc" >
                        <img :src="ticket.attachments[0].path" />
                    </div>
                  </div><!-- col_two -->
                </li>
              </ul>
            </div><!-- content -->
          </div><!-- booking_summary -->

        <div class="comments_block" v-permission="'view comments'" v-if="ticket.comments">
        <div class="title">{{__('Comments on ticket')}}</div>
        <div class="content">
          <div class="all_comments_items" v-if="ticket.comments.length">

            <div class="comment_item"  v-for="(comment,i) in ticket.comments" :key="i">

                    <div class="user_info">
                        <div class="user_account" v-if="comment && comment.author">
                        {{ comment.author.name }}
                        </div><!-- user_account -->


                        <time >{{comment.created_at}}</time>
                    </div><!-- user_info -->
                  <div class="desc" >{{ltrim(comment.body)}}</div>

            </div><!-- comment_item -->
          </div><!-- all_comments_items -->
          <div v-if="ticket.comments == 0 " class="no_comments">
            <div>
              <div class="icon"></div>
              <span>{{__('There is no comments')}}</span>
            </div>
          </div><!-- no_comments -->
          <div class="block_bottom">
            <button class="add_comment" @click="openCommentModal" v-permission="'add comments'">{{__('Add a comment')}}</button>
          </div><!-- block_bottom -->
        </div><!-- content -->
      </div><!-- comments_block -->
        </div><!-- right_col -->

      </div><!-- row_cols -->
    </div>

</template>

<script>

import Loading from 'vue-loading-overlay';

export default {
    title: 'show-ticket',
    components: {
        Loading
    },
    data() {
        return {
            comment:null,
            ticket:null,
            isLoading : true,
            crumbs : []
        }
    },
    computed:{

    },
    created(){
        this.getTicket();
    },
    methods: {
        ltrim(str) {
            if (str == null) return str;
            return str.trim();
        },
        openCommentModal(){
            this.$refs.commentModal.open();
        },
        getTicket() {
            let ticketId = this.$route.params.id;
            this.isLoading = true;
            axios
            .get('/nova-vendor/techincal-support/tickets/'+ticketId)
            .then(response => {
                console.log(response.data.success)
                if(response.data.success){
                    this.isLoading = false;
                    this.ticket = response.data.data
                }else{
                    this.isLoading = false;
                    return;
                }

            });
        },
        addComment(){
            let ticketId = this.$route.params.id;
            this.isLoading = true;
            axios
            .post('/nova-vendor/techincal-support/comment/'+ticketId,{comment:this.comment})
            .then(response => {
                if(response.data.success){
                    this.isLoading = false;
                    this.$toasted.show(this.__('comment added successfully !'), {type: 'success'})
                    this.$refs.commentModal.close();
                    this.getTicket();
                }else{
                    this.isLoading = false;
                    return;
                }

            });
        },
        validateForm(){
            if (!this.ticket.title || !this.ticket.body) {
                this.$toasted.show(this.__('Please fill ticket info'), {type: 'error'})
                this.isLoading = false;
                return false;
            }
            return true;
        }

    },
    mounted(){
       this.crumbs = [
                {
                    text: 'Home',
                    to: '/dashboards/main',
                },
                {
                    text: this.__('Technical support'),
                    to: '/techincal-support',
                },
                {
                  text: this.$route.params.id,
                  to : '#'
                }
            ] 
    }
};
</script>

<style lang="scss">
.row_cols .right_col .booking_summary {
    margin: 20px auto;
    padding: 0 10px;
}

.row_cols .right_col .booking_summary .title {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: justify;
    -ms-flex-pack: justify;
    justify-content: space-between;
}
.row_cols .right_col .booking_summary .content {
    width: auto;
    min-width: auto;
    max-width: none;
    background: #ffffff;
    border-radius: 5px;
    margin: 5px auto 0;
    border: 1px solid #ddd;
    padding: 10px;
    // -webkit-box-shadow: 0 2px 4px 0 rgb(0 0 0 / 5%);
    // box-shadow: 0 2px 4px 0 rgb(0 0 0 / 5%);
    min-height: 436px;
}

.row_cols .right_col .booking_summary .content ul li {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: start;
    -ms-flex-pack: start;
    justify-content: flex-start;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
    border-bottom: 1px solid #ddd;
    padding: 0 0 10px;
    margin: 0 auto 10px;
    font-size: 15px;
    color: #000;
}

.row_cols .right_col .booking_summary .content ul li .name {
    width: 20%;
    color: #666666;
}

.action_buttons {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin: 0 0 10px;
            button.add_receipts {
                display: block;
                background: #4099de;
                border: none;
                border-radius: 4px;
                color: #fff;
                font-size: 15px;
                padding: 5px 15px;
                &:hover {
                    background: #0071C9;
                } /* hover */
            } /* add_receipts */
            button {

                background: #4099de;
                border-radius: 5px;
                border: 1px solid #4099de;
                min-width: 100px;
                height: 35px;
                line-height: 35px;
                font-size: 15px;
                padding: 0 15px;
                color: #ffffff;
                width: 100%;
                -webkit-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
            } /* button */

        } /* action_buttons */

.row_cols .right_col .booking_summary .content ul li .col_two {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: justify;
    -ms-flex-pack: justify;
    justify-content: space-between;
    width: 80%;
}

.row_cols .right_col .booking_summary .content ul li .desc {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: start;
    -ms-flex-pack: start;
    justify-content: flex-start;
    color: #000;
    width: 80%;
}

    .comments_block {
      margin: 0 auto 20px;
      padding: 0;
      .title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        display: block;
        font-size: 20px;
        color: #000;
      } /* title */
      .content {
        width: auto;
        min-width: auto;
        max-width: none;
        background: #ffffff;
        border-radius: 5px;
        margin: 5px auto 0;
        border: 1px solid #ddd;
        padding: 10px;
        box-shadow: 0 2px 4px 0 rgba(0,0,0,.05);
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        min-height: 296px;
        .all_comments_items {
          width: 100%;
          min-height: 310px;
        } /* all_comments_items */
        .comment_item {
          display: flex;
          justify-content: space-between;
          align-items: center;
          width: 100%;
          flex-wrap: wrap;
          border: 1px solid #ddd;
          margin: 0 auto 10px;
          border-radius: 5px;
          padding: 5px;
          background: #fdfdfd;

          .desc {
            display: block;
            white-space: pre-line;
            font-size: 15px;
            color: #000;
            margin: 10px 0;
            width: 100%;
          } /* desc */
          .user_info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            .user_account {
              display: flex;
              align-items: center;
              justify-content: flex-start;
              font-size: 15px;
              color: #666666;
              img {
                display: block;
                width: 35px;
                height: 35px;
                border-radius: 100%;
                margin: 0 0 0 5px;
                border: 1px solid #ddd;
                [dir="ltr"] & {
                  margin: 0 5px 0 0;
                } /* ltr */
              } /* img */
            } /* user_account */
            time {
              display: block;
              font-size: 13px;
              color: #777777;
            } /* time */
          } /* user_info */
        } /* comment_item */
        .more_comments {
          background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' id='Capa_1' enable-background='new 0 0 515.555 515.555' height='512px' viewBox='0 0 515.555 515.555' width='512px' class=''%3E%3Cg%3E%3Cpath d='m496.679 212.208c25.167 25.167 25.167 65.971 0 91.138s-65.971 25.167-91.138 0-25.167-65.971 0-91.138 65.971-25.167 91.138 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23666666'/%3E%3Cpath d='m303.347 212.208c25.167 25.167 25.167 65.971 0 91.138s-65.971 25.167-91.138 0-25.167-65.971 0-91.138 65.971-25.167 91.138 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23666666'/%3E%3Cpath d='m110.014 212.208c25.167 25.167 25.167 65.971 0 91.138s-65.971 25.167-91.138 0-25.167-65.971 0-91.138 65.971-25.167 91.138 0' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23666666'/%3E%3C/g%3E%3C/svg%3E%0A");
          background-color: #fafafa;
          border: 1px solid #ddd;
          border-radius: 5px;
          height: 30px;
          margin: 0 auto 10px;
          background-size: 30px;
          background-repeat: no-repeat;
          background-position: center center;
          cursor: pointer;
          &:hover {
            background-color: #f5f5f5;
            border-color: #d8d8d8;
          } /* hover */
        } /* more_comments */
        .no_comments {
          min-height: 310px;
          width: 100%;
          text-align: center;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-wrap: wrap;
          .icon {
            background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' id='Capa_1' enable-background='new 0 0 512 512' height='512px' viewBox='0 0 512 512' width='512px'%3E%3Cg%3E%3Cg%3E%3Cpath d='m470.05 173.341h-62.573v-66.313c0-28.691-23.147-52.034-51.599-52.034h-192.423c-4.143 0-7.5 3.357-7.5 7.5s3.357 7.5 7.5 7.5h192.423c20.181 0 36.599 16.613 36.599 37.034v179.025c0 20.42-16.418 37.033-36.599 37.033h-271.867c-2.366 0-4.593 1.116-6.009 3.012l-52.655 70.491c-2.235 2.995-5.188 2.42-6.355 2.031-.937-.31-3.992-1.635-3.992-5.615v-285.978c0-20.421 16.418-37.034 36.599-37.034h79.339c4.143 0 7.5-3.357 7.5-7.5s-3.357-7.5-7.5-7.5h-79.339c-28.452 0-51.599 23.343-51.599 52.034v285.978c0 9.183 5.6 16.975 14.265 19.852 2.211.733 4.451 1.09 6.654 1.089 6.365 0 12.409-2.972 16.447-8.383l50.403-67.477h102.671v17.383c0 23.313 18.818 42.278 41.95 42.278h35.999c4.143 0 7.5-3.357 7.5-7.5s-3.357-7.5-7.5-7.5h-35.999c-14.86 0-26.95-12.237-26.95-27.278v-17.383h150.438c28.451 0 51.599-23.342 51.599-52.033v-97.712h62.573c14.86 0 26.95 12.242 26.95 27.29v223.374c0 1.886-1.248 2.582-1.992 2.828-.868.288-2.113.347-3.128-1.017l-41.128-55.058c-1.416-1.896-3.643-3.012-6.009-3.012h-143.593c-4.143 0-7.5 3.357-7.5 7.5s3.357 7.5 7.5 7.5h139.834l38.873 52.04c3.474 4.66 8.674 7.22 14.152 7.22 1.894-.001 3.82-.307 5.72-.937 7.454-2.472 12.271-9.171 12.271-17.065v-223.373c0-23.318-18.818-42.29-41.95-42.29z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m218.177 130.707h-139.872c-4.143 0-7.5 3.357-7.5 7.5s3.357 7.5 7.5 7.5h139.872c4.143 0 7.5-3.357 7.5-7.5s-3.358-7.5-7.5-7.5z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m298.828 197.382c0-4.143-3.357-7.5-7.5-7.5h-213.023c-4.143 0-7.5 3.357-7.5 7.5s3.357 7.5 7.5 7.5h213.023c4.143 0 7.5-3.358 7.5-7.5z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3Cpath d='m78.305 249.058c-4.143 0-7.5 3.357-7.5 7.5s3.357 7.5 7.5 7.5h184.085c4.143 0 7.5-3.357 7.5-7.5s-3.357-7.5-7.5-7.5z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%23DDDDDD'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
            background-size: 60px;
            width: 62px;
            height: 62px;
            display: block;
            margin: 0 auto;
            background-position: center center;
            background-repeat: no-repeat;
          } /* icon */
          span {
            display: block;
            width: 100%;
            font-size: 16px;
            margin: 7px auto 0;
            color: #dddddd;
          } /* span */
        } /* no_comments */
        .block_bottom {
          display: flex;
          justify-content: space-between;
          align-items: center;
          flex-wrap: wrap;
          width: 100%;
          align-self: end;
          button.add_comment {
            background: #4099de;
            border-radius: 5px;
            border: 1px solid #4099de;
            min-width: 100px;
            height: 35px;
            line-height: 35px;
            font-size: 15px;
            color: #ffffff;
            padding: 0 15px;
            -webkit-transition: all 0.2s ease-in-out;
            -moz-transition: all 0.2s ease-in-out;
            -o-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
            &:hover {
              background: #0071C9;
              border-color: #0071C9;
            } /* hover */
          } /* add_comment */
          button.more {
            background: #ffffff;
            border-radius: 5px;
            border: 1px solid #4099de;
            min-width: 100px;
            height: 35px;
            line-height: 35px;
            font-size: 15px;
            color: #4099de;
            padding: 0 15px;
            -webkit-transition: all 0.2s ease-in-out;
            -moz-transition: all 0.2s ease-in-out;
            -o-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
            &:hover {
              background: #4099de;
              color: #ffffff;
            } /* hover */
          } /* more */
        } /* block_bottom */
      } /* content */
    } /* comments_block */
</style>
