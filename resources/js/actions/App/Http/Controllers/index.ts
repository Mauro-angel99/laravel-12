import Auth from './Auth'
import Admin from './Admin'
import UserController from './UserController'
import ProfileController from './ProfileController'
import WorkPhaseController from './WorkPhaseController'
const Controllers = {
    Auth: Object.assign(Auth, Auth),
Admin: Object.assign(Admin, Admin),
UserController: Object.assign(UserController, UserController),
ProfileController: Object.assign(ProfileController, ProfileController),
WorkPhaseController: Object.assign(WorkPhaseController, WorkPhaseController),
}

export default Controllers