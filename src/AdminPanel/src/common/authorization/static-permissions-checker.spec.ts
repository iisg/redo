import {StaticPermissionsChecker} from "./static-permissions-checker";
import {User} from "../../users/user";

describe(StaticPermissionsChecker.name, () => {
  let currentUser: User;
  let checker: StaticPermissionsChecker;

  beforeEach(() => {
    currentUser = new User;
    currentUser.staticPermissions = ['A', 'B'];
    checker = new StaticPermissionsChecker(currentUser);
  });

  it("allows if all required permissions are present", () => {
    expect(checker.allAllowed(['A'])).toBeTruthy();
    expect(checker.allAllowed(['A', 'B'])).toBeTruthy();
  });

  it("allows if no permissions are required", () => {
    expect(checker.allAllowed([])).toBeTruthy();
  });

  it("forbids if some permission is missing", () => {
    expect(checker.allAllowed(['X'])).toBeFalsy();
    expect(checker.allAllowed(['A', 'X'])).toBeFalsy();
  });
});