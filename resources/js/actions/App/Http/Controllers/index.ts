import Auth from './Auth'
import Admin from './Admin'
import UserController from './UserController'
import ProfileController from './ProfileController'
import WorkPhaseController from './WorkPhaseController'
import WorkPhaseAssignmentController from './WorkPhaseAssignmentController'
const Controllers = {
    Auth: Object.assign(Auth, Auth),
Admin: Object.assign(Admin, Admin),
UserController: Object.assign(UserController, UserController),
ProfileController: Object.assign(ProfileController, ProfileController),
WorkPhaseController: Object.assign(WorkPhaseController, WorkPhaseController),
WorkPhaseAssignmentController: Object.assign(WorkPhaseAssignmentController, WorkPhaseAssignmentController),
}

export default Controllers