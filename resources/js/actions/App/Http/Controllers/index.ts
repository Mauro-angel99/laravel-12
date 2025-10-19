import Auth from './Auth'
import Admin from './Admin'
import ProfileController from './ProfileController'
import WorkPhaseController from './WorkPhaseController'
const Controllers = {
    Auth: Object.assign(Auth, Auth),
Admin: Object.assign(Admin, Admin),
ProfileController: Object.assign(ProfileController, ProfileController),
WorkPhaseController: Object.assign(WorkPhaseController, WorkPhaseController),
}

export default Controllers