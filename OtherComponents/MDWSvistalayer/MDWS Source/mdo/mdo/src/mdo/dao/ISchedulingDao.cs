using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public interface ISchedulingDao
    {
        HospitalLocation getClinicSchedulingDetails(String clinicId, String startDateTime);
        IList<Appointment> getPendingAppointments(string startDate);
        //string getClinicAvailability(string clinicId); // wrapped by getClinicSchedulingDetails
        IList<AppointmentType> getAppointmentTypes(string target);
        Appointment makeAppointment(Appointment appointment);
        Appointment cancelAppointment(Appointment appointment, string cancellationReason, string remarks);
        Appointment checkInAppointment(Appointment appointment);
        bool hasClinicAccess(string clinicId);
        bool isValidStopCode(string stopCodeId);
        bool hasValidStopCode(string clinicId);
        Dictionary<string, string> getCancellationReasons();
    }
}
