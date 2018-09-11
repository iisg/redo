<template>
    <form class="search-bar"
        v-on:submit.prevent="onSubmit ? onSubmit() : navigateToProcessedURL()">
        <div class="search-box-with-options">
            <div class="icon-with-input-box"
                :class="displayAsSmaller ? 'with-regular-font-size' : ''">
                <icon v-if="showLogo"
                    class="redo-logo-icon"
                    name="redo-logo"
                    :size="displayAsSmaller ? 1.5 : 2.5"></icon>
                <input type="text"
                    v-model="inputText"
                    :placeholder="placeholder">
            </div>
            <div v-if="showOptions"
                class="options">
                <radio-buttons-group name="search-options"
                    :labels="searchOptions"
                    v-model="selectedOption"></radio-buttons-group>
                <a :href="advancedSearchURL">
                    <icon name="settings-2"
                        size="1.25"></icon>
                    <span>Więcej kryteriów</span>
                </a>
            </div>
        </div>
        <button v-if="onSubmit"
            class="search-button"
            type="submit">
            <icon name="search"
                :size="displayAsSmaller ? 1.5 : 3"></icon>
        </button>
        <a v-else
            class="search-button"
            :href="inputText ? processedURL() : '#'">
            <icon name="search"
                :size="displayAsSmaller ? 1.5 : 3"></icon>
        </a>
    </form>
</template>

<script>
  import Icon from "./icon";
  import RadioButtonsGroup from "./radio-buttons-group";

  export default {
    components: {
      Icon,
      RadioButtonsGroup
    },
    props: {
      showLogo: {
        default: true
      },
      showOptions: {
        default: true
      },
      displayAsSmaller: {
        default: false
      },
      url: {
        default: "/search/%s"
      },
      advancedSearchURL: {
        default: "/advanced-search"
      },
      placeholder: {
        default: "Szukaj w zasobach repozytorium"
      },
      onSubmit: Function,
      phrase: {
        default: '',
      }
    },
    data() {
      return {
        inputText: '',
        searchOptions: [
          'Wszędzie',
          'Tytuł, autor'
        ],
        selectedOption: 'Wszędzie'
      };
    },
    mounted() {
      this.inputText = this.phrase || '';
    },
    methods: {
      processedURL() {
        return this.url.replace("%s", this.inputText);
      },
      navigateToProcessedURL() {
        window.location.href = this.processedURL();
      }
    }
  };
</script>

<style lang="scss">
    @import "../styles/colors";
    @import "../styles/variables";

    $bigger-then-regular-input-font-size: 18px;

    .search-bar {
        display: inline-block;
        display: inline-flex;
        align-items: center;
        .search-box-with-options {
            display: inline-block;
            .icon-with-input-box {
                display: inline-block;
                display: inline-flex;
                width: 100%;
                align-items: center;
                border: 1px solid $orange;
                border-radius: 3 * $default-gap-size / 4;
                &:not(.with-regular-font-size) {
                    .redo-logo-icon {
                        padding-left: $default-gap-size / 4;
                        padding-right: $default-gap-size / 4;
                    }
                    input {
                        min-width: 0;
                        margin: $default-gap-size / 2;
                        font-size: $bigger-then-regular-input-font-size;
                    }
                }
                &.with-regular-font-size {
                    .redo-logo-icon {
                        padding-left: $default-gap-size / 8;
                        padding-right: $default-gap-size / 8;
                    }
                    input {
                        margin: $default-gap-size / 4;
                    }
                }
                .redo-logo-icon {
                    margin-top: $default-gap-size / 4;
                    margin-bottom: $default-gap-size / 4;
                    border-right: 1px solid $orange;
                    color: $orange;
                }
                input {
                    width: 100%;
                    border: 0;
                    outline: 0;
                }
            }
            .options {
                display: flex;
                align-items: center;
                margin-top: $default-gap-size / 4;
                .radio-buttons-group {
                    label:last-child {
                        margin-right: $default-gap-size / 2;
                    }
                }
                a {
                    margin-left: auto;
                    padding-left: $default-gap-size / 4;
                    text-align: right;
                    color: inherit;
                    .icon, span {
                        margin-left: $default-gap-size / 4;
                    }
                    .icon {
                        color: $orange;
                    }

                }
            }
        }
        .search-button {
            display: inline-block;
            margin-left: $default-gap-size / 2;
            padding: $default-gap-size / 3;
            border-radius: 50%;
            background-color: $orange;
            color: white;
        }
        button.search-button {
            border: 0;
        }
    }
</style>
