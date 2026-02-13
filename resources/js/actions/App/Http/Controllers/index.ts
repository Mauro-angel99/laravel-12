import Auth from './Auth'
import Admin from './Admin'
import UserController from './UserController'
import ProfileController from './ProfileController'
import WorkPhaseController from './WorkPhaseController'
import WorkPhaseAssignmentController from './WorkPhaseAssignmentController'
import WarehouseController from './WarehouseController'
import WorkParameterController from './WorkParameterController'
import FilePathSettingController from './FilePathSettingController'
import WorkPhaseImageController from './WorkPhaseImageController'
import NonConformityController from './NonConformityController'
import Settings from './Settings'
const Controllers = {
    Auth: Object.assign(Auth, Auth),
Admin: Object.assign(Admin, Admin),
UserController: Object.assign(UserController, UserController),
ProfileController: Object.assign(ProfileController, ProfileController),
WorkPhaseController: Object.assign(WorkPhaseController, WorkPhaseController),
WorkPhaseAssignmentController: Object.assign(WorkPhaseAssignmentController, WorkPhaseAssignmentController),
WarehouseController: Object.assign(WarehouseController, WarehouseController),
WorkParameterController: Object.assign(WorkParameterController, WorkParameterController),
FilePathSettingController: Object.assign(FilePathSettingController, FilePathSettingController),
WorkPhaseImageController: Object.assign(WorkPhaseImageController, WorkPhaseImageController),
NonConformityController: Object.assign(NonConformityController, NonConformityController),
Settings: Object.assign(Settings, Settings),
}

export default Controllers