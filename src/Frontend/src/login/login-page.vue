<template>
    <div>
        <div class="main-container">
            <div class="login-container">
                <div class="login-box">
                    <div class="logo-box">
                        <redo-logo class="logo"></redo-logo>
                    </div>
                    <form class="form-box"
                        method="post">
                        <input type="hidden"
                            name="_csrf_token"
                            :value="csrfToken">
                        <div>
                            <div>
                                <h3 class="login-text administration-label">ADMINISTRACJA REDO</h3>
                            </div>
                        </div>
                        <div class="login-error-element alert alert-dismissible alert-danger"
                            v-if="error">
                            <button type="button"
                                class="close"
                                data-dismiss="alert">×
                            </button>
                            <strong>Logowanie nieudane.</strong>
                        </div>
                        <div class="input-elements">
                            <div class="form-group username-input">
                                <svg class="login-box-icon"
                                    viewBox="0 0 1 1">
                                    <use xlink:href="/files/icons.svg#user-2"></use>
                                </svg>
                                <input name="_username"
                                    type="text"
                                    id="username"
                                    class="form-control input-md"
                                    :value="lastUsername">
                            </div>
                            <div class="form-group">
                                <svg class="login-box-icon"
                                    viewBox="0 0 1 1">
                                    <use xlink:href="/files/icons.svg#key"></use>
                                </svg>
                                <input name="_password"
                                    type="password"
                                    id="password"
                                    class="form-control input-md">
                                <button class="submit-button"
                                    type="submit"
                                    name="Zaloguj">
                                    <svg class="login-box-icon"
                                        viewBox="0 0 1 1">
                                        <use xlink:href="/files/icons.svg#accept-2"></use>
                                    </svg>
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="information-links-box">
                    <a class="information-link-element">
                        <svg class="information-link-icon"
                            viewBox="0 0 1 1">
                            <use xlink:href="/files/icons.svg#help"></use>
                        </svg>
                        <span class="information-link-text">przypomnij hasło</span>
                    </a>
                    <a class="information-link-element">
                        <svg class="information-link-icon"
                            viewBox="0 0 1 1">
                            <use xlink:href="/files/icons.svg#mail"></use>
                        </svg>
                        <span class="information-link-text">kontakt z administratorem</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  import RedoLogo from "../common/redo-logo";

  export default {
    props: ['csrfToken', 'lastUsername', 'error'],
    components: {RedoLogo}
  };
</script>

<style lang="scss">
    $current-theme-color: #f9b000;
    $background-color: #e6e6e6;
    $information-link-elements-color: #666;

    $login-box-padding: 30px;
    $icon-width: 2em;
    $input-margin: 15px;
    $username-input-right-margin: $icon-width;
    $logo-width: 10em;
    $login-container-max-width: 600px;
    $center-administration-label: 455px;
    $text-font-size: 23.8px;
    $text-font-family: "Open Sans", sans-serif;

    .main-container {
        background-color: $background-color;
        height: 100vh;
        display: flex;
        align-items: center;

        .login-container {
            background-color: $background-color;
            margin-right: auto;
            margin-left: auto;
            width: $login-container-max-width;

            .login-box {
                padding: $login-box-padding;
                display: flex;
                flex-wrap: wrap;
                background-color: #4c4c4c;
                .logo-box {
                    margin: auto;
                    text-align: center;
                    .logo {
                        margin: auto;
                        height: $logo-width;
                        fill: $current-theme-color;
                    }
                }

                .form-box {
                    flex: 1;
                    padding: 15px;
                    .login-error-element {
                        border-radius: 0;
                    }
                    .administration-label {
                        color: $current-theme-color;
                        font-weight: bold;
                    }
                    .input-elements {
                        .username-input {
                            margin-right: $username-input-right-margin;
                        }
                        .form-group {
                            display: flex;
                            align-items: center;
                            .form-control {
                                margin-right: $input-margin;
                                border-radius: 0;
                                flex: 1;
                                margin-left: $input-margin;
                                &:focus {
                                    box-shadow: inset 0 1px 1px $current-theme-color, 0 0 8px $current-theme-color;
                                    border-color: $current-theme-color;
                                }
                            }
                            .login-box-icon {
                                display: block;
                                height: $icon-width;
                                fill: $current-theme-color;
                            }
                            .submit-button {
                                padding: 0;
                                border: none;
                                background: none;
                                outline: none;
                                &:hover {
                                    filter: drop-shadow(0px 0px 5px $current-theme-color)
                                }
                            }

                        }
                    }
                }
            }

            .login-text {
                @media (max-width: $center-administration-label) {
                    text-align: center;
                }
                font-size: $text-font-size;
                font-family: $text-font-family;
            }

            .information-links-box {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-evenly;
                .information-link-element {
                    display: inherit;
                    align-items: center;
                    margin-top: 15px;
                    text-decoration: none;
                    &:hover {
                        filter: drop-shadow(0px 0px 5px $information-link-elements-color)
                    }
                    .information-link-icon {
                        height: $icon-width;
                        display: block;
                        padding-right: 15px;
                        fill: $information-link-elements-color;
                    }
                    .information-link-text {
                        flex: 1;
                        color: $information-link-elements-color;
                        font-weight: normal;
                        font-size: $text-font-size;
                        vertical-align: bottom;
                    }
                }
            }
        }
    }
</style>
