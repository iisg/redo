export class Entity { // Why not abstract? See explanation at the bottom.
  hovered: boolean = false;
  editing: boolean = false;
  pendingRequest: boolean = false;
}

/*
 So why isn't Entity an abstract class? Please sit down and fasten your seatbelts before reading further.
 Karma tests won't pass if this file exports an abstract class, doesn't end with another export and is imported in tests. Seriously.
 So we either have to make Entity non-abstract or put some dummy export at the end of this file.
 I've tried to pinpoint it for way too long now, but I've narrowed it down to one of these packages:
 jasmine-core@2.8.0 jspm@0.16.48 karma@1.7.0 karma-systemjs@^0.16.0 systemjs@^0.19.35 typescript@2.2.2
 Don't believe me? Do I sound insane? Try it yourself.
 */
// export const WHAT_THE_HECK = undefined;
