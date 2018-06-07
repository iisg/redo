import {User} from "users/user";
import {UserRoleChecker} from "./user-role-checker";

describe(UserRoleChecker.name, () => {
  let currentUser: User;
  let checker: UserRoleChecker;

  beforeEach(() => {
    currentUser = new User();
    currentUser.roles = ['A', 'B', 'C'];
    checker = new UserRoleChecker(currentUser);
  });

  it("allows if all required roles are present", () => {
    expect(checker.hasAll(['B'])).toBeTruthy();
    expect(checker.hasAll(['B', 'C'])).toBeTruthy();
  });

  it("allows if no roles are required", () => {
    expect(checker.hasAll([])).toBeTruthy();
  });

  it("forbids if some roles is missing", () => {
    expect(checker.hasAll(['X'])).toBeFalsy();
    expect(checker.hasAll(['A', 'X'])).toBeFalsy();
  });
});
