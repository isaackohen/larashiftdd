<template>
   <div class="gameCategory">
		<div class="header">
            {{ $t('general.sidebar.all') }}
         </div>
      <template v-if="!gamesLoading">
		  <div class="index_cat">
			 <i class="fas fa-search"></i> {{ $t('general.sidebar.search') }}
		  </div>
		  
			<div class="container-fluid" style="max-width: 1375px;">
			   <div class="search">
				  <div class="row-list">
					 <div class="col-12 col-sm-10 col-md-10">
						<input v-model="keyword" autocomplete="off" type="text" class="lobby search-input" :placeholder="'Search in ' + count + ' games..'" name="">
					 </div>
					 <div class="col-12 col-sm-2 col-md-2">
						<div class="row-list">
						   <div class="provider-select-menu">
							  <button @click="openProviders" class="btn btn-primary searchbar"><i :class="'fas ' + (ProvidersList ? 'fa-chevron-up' : 'fa-chevron-down')" aria-hidden="true"></i> Provider</button>		
						   </div>
						   <!--
						   <div class="categories-select-menu">
							  <button @click="openCategories" class="btn btn-primary searchbar"><i :class="'fas ' + (CategoriesList ? 'fa-chevron-up' : 'fa-chevron-down')" aria-hidden="true"></i> Select Categories</button>		
						   </div>
						   !-->
						</div>
					 </div>
					 <div class="list-providers" :class="{ 'show' : ProvidersList }">
						<div class="custom-dropdown">
							<template v-for="provider in Providers">
							   <div class="custom-control custom-checkbox">
									<div @click="selectProvider(provider.name)" :class="'custom-select-option ' + (checkedProviders.includes(provider.name) ? 'selected' : '')">{{ provider.name }}</div>
							   </div>
							</template> 
						</div>
					 </div>
					<div class="list-categories" :class="{ 'show' : CategoriesList }">
						<div class="custom-dropdown">
						   <div class="custom-control custom-checkbox">
								<div @click="selectCategory('inhouse')" :class="'custom-select-option ' + (checkedCategories.includes('inhouse') ? 'selected' : '')">In-House</div>
						   </div>
						   <div class="custom-control custom-checkbox">
								<div @click="selectCategory('slots')" :class="'custom-select-option ' + (checkedCategories.includes('slots') ? 'selected' : '')">Slots</div>
						   </div>
						   <div class="custom-control custom-checkbox">
								<div @click="selectCategory('live-table')" :class="'custom-select-option ' + (checkedCategories.includes('live-table') ? 'selected' : '')">Live Tables</div>
						   </div>
						   <div class="custom-control custom-checkbox">
								<div @click="selectCategory('live')" :class="'custom-select-option ' + (checkedCategories.includes('live') ? 'selected' : '')">Live</div>
						   </div>
						   <div class="custom-control custom-checkbox">
								<div @click="selectCategory('scratch-cards')" :class="'custom-select-option ' + (checkedCategories.includes('scratch-cards') ? 'selected' : '')">Scratchcards</div>
						   </div>
							<div class="custom-control custom-checkbox">
								<div @click="selectCategory('multiplayer')" :class="'custom-select-option ' + (checkedCategories.includes('multiplayer') ? 'selected' : '')">Multiplayer</div>
						   </div>
						   
						   <div class="custom-control custom-checkbox">
								<div @click="selectCategory('virtualsport');selectCategory('virtualsports')" :class="'custom-select-option ' + ((checkedCategories.includes('virtualsport') && checkedCategories.includes('virtualsports')) ? 'selected' : '')">Virtual Sports</div>
						   </div>
						   <div class="custom-control custom-checkbox">
								<div @click="selectCategory('rollback')" :class="'custom-select-option ' + (checkedCategories.includes('rollback') ? 'selected' : '')">RollBack</div>
						   </div>
							<div class="custom-control custom-checkbox">
								<div @click="selectCategory('Video Slots')" :class="'custom-select-option ' + (checkedCategories.includes('Video Slots') ? 'selected' : '')">Video Slots</div>
						   </div>
						</div>
					 </div>
				  </div>
			   </div>
			</div>
		 
			 <div class="container-fluid" style="max-width: 1420px; margin: 0px auto;">
				<template v-if="!pageLoading">
					<div class="warning" v-if="Object.keys(categoryGames).length === 0" v-html="$t('general.sidebar.no_search')"></div>
				 </template>
			   <template v-for="(cat, key) in categoryGames">
				  <div class="games">
					 <div v-for="game in cat" :key="game.id" :class="`game_poster_${game.type} game-${game.id} game_type-${game.type} hvr-float-shadow`">
						<div :class="`game_poster_${game.type}-image game_tp-image`" v-if="game.ext" :style="`background: url('https://games.cdn4.dk/games${game.icon}?auto=format&fit=crop&sharp=10&w=205&h=137&q=70') no-repeat !important; background-position-x: center !important;`"  @click="game.ext ? $router.push(`/casino/${game.id}`) : $router.push(`/game/${game.id}`)">
						   <div :class="`game_poster_${game.type}-provider`" v-if="game.ext" @click="game.ext ? $router.push(`/casino/${game.id}`) : $router.push(`/game/${game.id}`)">
							  {{ game.p }}
						   </div>
						</div>
						<div :class="`game_poster_${game.type}-image game_tp-image`" v-if="!game.ext" @click="game.ext ? $router.push(`/casino/${game.id}`) : $router.push(`/game/${game.id}`)" :style="`background: url('https://cdn.davidkohen.com/provablyfair/${game.id}.png?q=95&sharp=5&w=205&h=145&fit=crop&usm=5&fm=png') no-repeat !important; background-position-x: center !important;`">
						   <div class="unavailable" v-if="game.d">
							  <div class="slanting">
								 <div class="content">
									{{ $t(game.isPlaceholder ? 'general.coming_soon' : 'general.not_available') }}
								 </div>
							  </div>
						   </div>
						</div>
						<div :class="`game_poster_${game.type}-houseEdge`" v-if="game.houseEdge" @click="game.ext ? $router.push(`/casino/${game.id}`) : $router.push(`/game/${game.id}`)">
						   {{ game.houseEdge }}% House Edge
						</div>
						<div :class="`game_poster_${game.type}-label`" v-if="game.ext"  @click="game.ext ? $router.push(`/casino/${game.id}`) : $router.push(`/game/${game.id}`)">
						   {{ game.name }}
						</div>
						<div :class="`game_poster_${game.type}-name`" v-if="!game.ext"  @click="game.ext ? $router.push(`/casino/${game.id}`) : $router.push(`/game/${game.id}`)">
						   {{ game.name }}
						</div>
						<div :class="`game_poster_${game.type}-footer`" @click="toggleFavoriteGame(game.id)" v-if="!isGuest">
						   <template v-if="!favMarkLoading">
							  <i :class="`fa${!user.user.favoriteGames || !user.user.favoriteGames.includes(game.id) ? 'l' : 's'} fa-star`"></i>
							  {{ $t(!user.user.favoriteGames || !user.user.favoriteGames.includes(game.id) ? 'general.sidebar.mark_as_favorite' : 'general.sidebar.remove_from_favorite') }}
						   </template>
						   <loader v-else></loader>
						</div>
					 </div>
				  </div>
			   </template>
			</div>
		 
         <template v-if="!pageLoading">
            <template v-if="(page * depth < count)">
               <div class="divider">
                  <div class="line"></div>
                  <div class="divider-title">
                     <div class="show-more_progress-track">
                        <div :style="{ width: ((page * depth / count) * 100) + '%' }" class="show-more_progress-bar"></div>
                     </div>
                     <div class="show-more_text">Shown <span> {{ page * depth }} </span> from <span>{{ count }}</span> games</div>
                     <button @click="pageLoad()" href="javascript:void(0)" class="btn show-more btn-primary"><i class="fas fa-random" aria-hidden="true"></i> Show more</button>				
                  </div>
                  <div class="line"></div>
               </div>
            </template>
         </template>
         <div v-else class="games-load">
            <loader></loader>
         </div>
      </template>
      <div v-else class="games-load">
         <loader></loader>
      </div>
   </div>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        data() {
            return {
                categoryGames: {},
				gamesData: [],
				gamesLoading: true,
				pageLoading: true,
				page: 0,
				depth: 30,
				count: 0,
				favMarkLoading: false,
				ProvidersList: false,
				CategoriesList: false,
				checkedProviders: [],
				checkedCategories: [],
				Providers: [],
				keyword: null,
				timer: 0
            }
        },
        computed: {
            ...mapGetters(['user', 'isGuest'])
        },
		watch: {
            gamesData() {
				this.categoryGames = {};
                this.load();
            },
			keyword(after, before) {
			    if (this.timer) {
					clearTimeout(this.timer);
					this.timer = null;
				}
				this.timer = setTimeout(() => {	
					this.gamesData = [];
					this.categoryGames = {};
					this.page = 0;
					this.pageLoad();
				}, 800);
			},
			checkedProviders() {
				this.gamesData = [];
				this.categoryGames = {};
				this.page = 0;
				this.pageLoad();
			},
			checkedCategories() {
				this.gamesData = [];
				this.categoryGames = {};
				this.page = 0;
				this.pageLoad();
			}
        },
        created() {
			axios.post('/api/data/providers').then(({ data }) => {
                this.Providers = data[0].providers;
            });
			var payload = {
				page: this.page
			};
			axios.post('/api/data/games', payload).then(({ data }) => {
				this.page += 1;
				this.gamesData = data[0].games;
				this.count = data[0].count;
				if(this.gamesData.length === 0) {
					this.$router.push('/404');
					return;
				}
				this.gamesLoading = false;
				this.pageLoading = false;
			});
			this.load();
        },
		methods: {
            load() {			
				let validateUrlCategory = true;
				let games = this.gamesData;
				let duplicates = [];
				_.forEach(games, (game) => {
					if (duplicates.includes(game.id)) return;
					duplicates.push(game.id);

					if (!this.categoryGames['all']) this.categoryGames['all'] = [game];
					else this.categoryGames['all'].push(game);
				});
            }, 
			pageLoad() {
				this.pageLoading = true;
				this.categoryGames = {};
				var payload = {
					page: this.page,
					text: this.keyword,
					category: this.checkedCategories,
					provider: this.checkedProviders
				};
				axios.post('/api/data/games', payload).then(({ data }) => {
					this.page += 1;
					this.gamesData = this.gamesData.concat(data[0].games);
					this.count = data[0].count;
					this.pageLoading = false;
				});
				this.load();
			},
			toggleFavoriteGame(id) {
                if(this.favMarkLoading) return;
                this.favMarkLoading = true;
                axios.post('/api/user/markGameAsFavorite', { id: id }).then(() => {
                    this.$store.dispatch('update');
                    this.favMarkLoading = false;
                }).catch(() => this.favMarkLoading = false);
            },
			openProviders() {
				this.CategoriesList ? (this.CategoriesList = false) : (this.CategoriesList = false);
				this.ProvidersList = !this.ProvidersList;
			},
			openCategories() {
				this.ProvidersList ? (this.ProvidersList = false) : (this.ProvidersList = false);
				this.CategoriesList = !this.CategoriesList;
			},
			selectProvider(item) {
				if(this.checkedProviders.indexOf(item) != -1 ) {
					this.checkedProviders.splice((this.checkedProviders.indexOf(item)), 1);
				}  else {
					this.checkedProviders.push(item);
				}
			},
			selectCategory(item) {
				if(this.checkedCategories.indexOf(item) != -1 ) {
					this.checkedCategories.splice((this.checkedCategories.indexOf(item)), 1);
				}  else {
					this.checkedCategories.push(item);
				}
			}
        }
    }
</script>

<style lang="scss" scoped>
    @import "resources/sass/variables";
	
	.search {
		width: auto;
		margin-bottom: auto;
		margin-top: 20px;
		min-height: 60px;
		padding: 10px;
		justify-content: center;
		border-radius: 16px;
		
		.custom-select-option {
			display: flex;
			align-items: center;
			font-size: medium;
			position: relative;
			cursor: pointer;
			text-transform: capitalize;
		}

		.custom-select-option::before {
			content: '';
			display: flex;
			justify-content: center;
			align-items: center;
			width: 1.2rem;
			height: 1.2rem;
			border-radius: .25rem;
			@include themed() {
				border: 1px solid darken(t('secondary'), 20%);
			}
			margin-right: .5rem;
			flex-shrink: 0;
		}

		.custom-select-option.selected::before {
			@include themed() {
				border-color: t('secondary');
			}
		}

		.custom-select-option.selected::after {
			content: '';
			display: block;
			width: .7rem;
			height: .7rem;
			border-radius: .15rem;
			@include themed() {
				background-color: t('secondary');
			}
			position: absolute;
			left: calc(.25rem)
		}
					
		.row-list {
		    display: flex;
			flex-wrap: wrap;
			
			.col-md-10,.col-md-2 {
				position: relative;
				width: 100%;
				padding-right: 5px;
				padding-left: 5px;
			}
			
			@media (min-width: 768px) {
				.col-md-10 {
					flex: 0 0 80%;
					max-width: 80%;
				}
				
				.col-md-2 {
					flex: 0 0 20%;
					max-width: 20%;
				}
			}
			
		}
		
		@media (max-width: 768px) {
			.row-list {
			    margin-right: -15px;
				margin-left: -15px;
			}
		}
		
		.search-input {
			width: 100%;
			caret-color: #ffffff;
			font-size: 14px;
			font-weight: 300;
			border-radius: 16px;
			color: #fff;
			transition: width 0.4s linear;
			border-radius: .15rem;
			@include themed() {
				background: t('header');
			}
			margin-top: 5px;
			height: 100%;
		}
		
		input {
			border: 1px solid #212121;
		}
		
		.provider-select-menu,.categories-select-menu {
			margin: 10px 10px 0px 10px;
			
			.searchbar {
				padding: 12px 10px;
			}
			
		}
		
		.list-providers {
			width: 100%;
			margin-bottom: 20px;
			display: none;
			
			.custom-dropdown {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(8rem, 9rem));
				position: relative;
				top: 20px;
				@include themed() {
					background: rgba(t('sidebar'), .8);
				}
				opacity: 1;
				gap: 0.6rem 1rem;
				padding: 1.2rem;
				border-radius: 0.625rem;
				transition: opacity 150ms ease 0s;
				justify-content: space-evenly;
			}
			
		}
		
		.list-categories {
			width: 100%;
			margin-bottom: 20px;
			display: none;
			
			.custom-dropdown {
				display: flex;
				position: relative;
				top: 20px; 
				@include themed() {
					background: rgba(t('sidebar'), .8);
				}
				opacity: 1;
				gap: 0.6rem 1rem;
				padding: 1.2rem;
				border-radius: 0.625rem;
				transition: opacity 150ms ease 0s;
				flex-direction: row;
				flex-wrap: wrap;
				justify-content: space-evenly;
			}
			
		}
		
		.custom-dropdown .custom-control {
			position: relative;
			z-index: 1;
			display: block;
			min-height: 1.08rem;
			-webkit-print-color-adjust: exact;
			user-select: none;
		}
		
		.list-providers.show,.list-categories.show {
		    display: block;
		}
		
	}
	
	.games-load {
		display: flex;
		align-items: center;
		justify-content: center;
		margin-top: 30px;
	}
	
	.show-more_progress-track {
		@include themed() {
			background-color: darken(t('secondary'), 20%);
		}
	}
	
	.show-more_progress-bar {
		height: 0.5rem;
		border-radius: 0.5rem;
		@include themed() {
			background-color: t('secondary');
		}
	}
	
	.show-more_text {
		font-size: 0.7rem;
		margin-bottom: 10px;
	}
	
	.show-more_progress-track {
		width: 10rem;
		max-width: 100%;
		height: 0.5rem;
		border-radius: 0.5rem;
		margin-top: 20px;
		margin-bottom: 15px;
	}
	
	.btn.show-more {
		padding: 8px 20px;
	}

    .warning {
        width: 100%;
        text-align: center;
        font-size: 1.1em;
        margin-top: 15px;
        margin-bottom: 15px;
     }

    .gameCategory {
        @include themed() {
            .header {
                background: rgba(t('sidebar'), .8);
                backdrop-filter: blur(20px);
                border-bottom: 2px solid t('border');
                margin-top: -15px;
                padding: 30px 35px;
                font-size: 1.5em;
                position: static;
                top: 73px;
                z-index: 555;
            }
        }
    }
	
	.index_cat {
        font-size: 1.15em;
        font-weight: 600;
        margin-bottom: 15px;
        margin-top: 25px;
        padding-left: 40px;
        display: flex;
        flex-direction: row;
        align-items: center;

        i {
            margin-right: 5px;
            width: 20px;
            text-align: center;
        }
    }

    .game_poster_local, .game_poster_external, .game_placeholder {
        display: inline-flex;
        flex-direction: column;
        width: 205px;
        min-width: 205px;
        height: 137px;
        border-radius: 7px;
        position: relative;
        box-shadow: 0px 7px 10px rgb(0 0 0 / 50%);

        @include themed() {
            background: rgba(t('sidebar'), .82);
            backdrop-filter: blur(20px);
        }


        &.game_type-local {
            background-size: cover !important;
            background-position: center !important;
            height: 145px !important;
            &-image {
                height: 100% !important;
            }
        }


        &.game_type-external {
            background-size: cover !important;
            background-position: center !important;
            &-image {
                height: 100% !important;
            }
        }

        &-image {
            width: 100%;
            height: 100%;
            border-radius: 5px;
            position: relative;
            z-index: 5;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            transform: scale(1);
            transition: all 0.4s ease;

            .game_tp-image {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                z-index: 1;
            }


        }

        &-provider {
            font-size: 0.82em;
            position: absolute;
            bottom: 55px;
            right: 0;

            @include themed() {
                background: linear-gradient(to right, transparent 0%, t('sidebar') 170%);
            }

            padding: 10px 25px;
            padding-right: 10px;
        }


        &-houseEdge {
            font-size: 0.8em;
            position: absolute;
            bottom: 55px;
            right: 0;

            @include themed() {
                background: linear-gradient(to right, transparent 0%, t('sidebar') 170%);
            }

            padding: 10px 25px;
            padding-right: 10px;
        }

        &-name {
            position: absolute;
            bottom: -1px;
            left: -1px;
            height: 45px;
            z-index: 7;
            border-bottom-left-radius: 7px;
            border-bottom-right-radius: 7px;
            padding: 10px 20px;
            @include themed() {
            background: rgba(t('sidebar'), .65);
            backdrop-filter: blur(1px);
            }
            width: 101%;
            opacity: 0;
            transition: opacity .3s ease;
            transition-delay: .1s;
            font-size: 0.8em;
            display: flex;
            align-items: center;

            i {
                margin-right: 5px;
            }
        }


        &-label {
            position: absolute;
            bottom: -1px;
            left: -1px;
            height: 30px;
            z-index: 7;
            border-bottom-left-radius: 7px;
            border-bottom-right-radius: 7px;
            padding: 15px 20px;
            @include themed() {
            background: rgba(t('sidebar'), .65);
            backdrop-filter: blur(2px);
            }
            width: 101%;
            opacity: 0;
            transition: opacity .3s ease;
            transition-delay: .1s;
            font-size: 0.8em;
            display: flex;
            align-items: center;

            i {
                margin-right: 5px;
            }
        }

        &-footer {
            position: absolute;
            top: 0;
            left: -1px;
            height: 25px;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
            z-index: 7;
            padding: 15px 20px;
            @include themed() {
            background: rgba(t('sidebar'), .65);
            backdrop-filter: blur(1px);
            }
            width: 101%;
            opacity: 0;
            transition: opacity .3s ease;
            transition-delay: .1s;
            font-size: 0.8em;
            display: flex;
            align-items: center;

            .loaderContainer {
                transform: scale(.2) translate(-200%);
            }

            i {
                margin-right: 5px;
            }
        }

        @include only_safari('.game_poster-name', (
            font-weight: unset
        ));

        &-houseEdge {
            z-index: 6;
        }

        .vue-content-placeholders-img {
            height: 100% !important;
        }

        margin-bottom: 10px;
        margin-right: 6px;
        margin-left: 6px;
        z-index: 1;
        transition: all 0.3s ease;

        &:hover {
            z-index: 5;

            .game_poster_local-footer, .game_poster_external-footer, .game_poster_local-name {
                opacity: 1;

                &-provider {
                            @include themed() {
                                background: linear-gradient(to right, transparent 0%, t('sidebar') 70%);
                            }
                }
            }
            .game_poster_local-label, .game_poster_external-label, .game_poster_local-name {
                opacity: 1;
            }
        }

        cursor: pointer;
        border-radius: 5px;
    }

    .game_poster {
        .unavailable {
            z-index: 4;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(black, 0.4);
            color: white;

            .slanting {
                transform: skewY(-5deg) translateY(-50%);
                padding: 25px;
                position: absolute;
                top: 50%;
                background: rgba(black, 0.85);
                width: 100%;

                .content {
                    font-size: 15px;
                    transform: skewY(5deg);
                    text-align: center;
                }
            }
        }
    }

    .game-bullvsbear {
        &-image {
            &:after {
                background-image: url('/img/game/bullvsbear.svg');
                background-color: #3178f4;
            }
        }
    }



    .games {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }

    @include media-breakpoint-down(md) {		
        .game_poster_local, .game_poster_external, .game_placeholder {
            width: calc(22%) !important;
			min-width: unset !important;

            &-image {
                height: 100% !important;
            }
        }
    }

    @include media-breakpoint-down(sm) {	
	
		.searchbar {
			padding: 12px 12px !important;
		}
	
        .game_poster_local, .game_poster_external, .game_placeholder {
            width: calc(40%) !important;
            min-width: unset !important;
            max-width: 205px !important;

            &-image {
                height: 100% !important;
               }
        }

        .image {
            width: 100%;
            background-position: center;
        }

        .slideContent .description {
            width: calc(100% - 15px) !important;
            margin-bottom: 15px !important;
        }

        .slideContent .header {
            margin-bottom: 15px !important;
        }

        .glide__bullets, .glide__arrows {
            display: none;
        }
    }

    @media(max-width: 450px) {
        .index_cat {
            padding-left: 0 !important;
        }
        .game_poster_external-label {
            opacity: 1;
        }

        .game_poster_local, .game_poster_external, .game_placeholder {
            margin-right: 8px !important;
            width: 44% !important;
            background-position-y: center !important;
        }
    }
</style>
