using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public interface IEncounterDao
    {
        Appointment[] getAppointments();
        Appointment[] getAppointments(string pid);
        Appointment[] getFutureAppointments();
        Appointment[] getFutureAppointments(string pid);
        Appointment[] getAppointments(int pastDays, int futureDays);
        Appointment[] getAppointments(string pid, int pastDays, int futureDays);
        Appointment[] getMentalHealthAppointments();

        string getAppointmentText(string apptId);
        string getAppointmentText(string pid, string apptId);
        Adt[] getInpatientMoves(string fromDate, string toDate);
        Adt[] getInpatientMovesByCheckinId(string checkinId);
        Adt[] getInpatientMoves();
        Adt[] getInpatientMoves(string pid);
        Adt[] getInpatientMoves(string fromDate, string toDate, string iterLength); 

        // Depreciate to lookupHospitalLocations
        HospitalLocation[] lookupLocations(string target, string direction);
        StringDictionary lookupHospitalLocations(string target);

        string getLocationId(string locationName);
        HospitalLocation[] getWards();
        HospitalLocation[] getClinics(string target, string direction);
        InpatientStay[] getStaysForWard(string wardId);
        Drg[] getDRGRecords();
        Visit[] getOutpatientVisits();
        Visit[] getOutpatientVisits(string pid);
        Visit[] getVisits(string fromDate, string toDate);
        Visit[] getVisits(string pid, string fromDate, string toDate);
        Visit[] getVisitsForDay(string theDate);
        Visit[] getMentalHealthVisits();

        InpatientStay[] getAdmissions();
        InpatientStay[] getAdmissions(string pid);
        string getServiceConnectedCategory(string initialCategory, string locationIen, bool outpatient);
        string getOutpatientEncounterReport(string fromDate, string toDate, int nrpts);
        string getOutpatientEncounterReport(string pid, string fromDate, string toDate, int nrpts);
        string getAdmissionsReport(string fromDate, string toDate, int nrpts);
        string getAdmissionsReport(string pid, string fromDate, string toDate, int nrpts);
        string getExpandedAdtReport(string fromDate, string toDate, int nrpts);
        string getExpandedAdtReport(string pid, string fromDate, string toDate, int nrpts);
        string getDischargesReport(string fromDate, string toDate, int nrpts);
        string getDischargesReport(string pid, string fromDate, string toDate, int nrpts);
        string getTransfersReport(string fromDate, string toDate, int nrpts);
        string getTransfersReport(string pid, string fromDate, string toDate, int nrpts);
        string getFutureClinicVisitsReport(string fromDate, string toDate, int nrpts);
        string getFutureClinicVisitsReport(string pid, string fromDate, string toDate, int nrpts);
        string getPastClinicVisitsReport(string fromDate, string toDate, int nrpts);
        string getPastClinicVisitsReport(string pid, string fromDate, string toDate, int nrpts);
        string getTreatingSpecialtyReport(string fromDate, string toDate, int nrpts);
        string getTreatingSpecialtyReport(string pid, string fromDate, string toDate, int nrpts);
        string getCareTeamReport();
        string getCareTeamReport(string pid);
        string getDischargeDiagnosisReport(string fromDate, string toDate, int nrpts);
        string getDischargeDiagnosisReport(string pid, string fromDate, string toDate, int nrpts);
        IcdReport[] getIcdProceduresReport(string fromDate, string toDate, int nrpts);
        IcdReport[] getIcdProceduresReport(string pid, string fromDate, string toDate, int nrpts);
        IcdReport[] getIcdSurgeryReport(string fromDate, string toDate, int nrpts);
        IcdReport[] getIcdSurgeryReport(string pid, string fromDate, string toDate, int nrpts);
        string getCompAndPenReport(string fromDate, string toDate, int nrpts);
        string getCompAndPenReport(string pid, string fromDate, string toDate, int nrpts);
        DictionaryHashList getSpecialties();
        DictionaryHashList getTeams();
        Adt[] getInpatientDischarges(string pid);
        InpatientStay[] getStayMovementsByDateRange(string fromDate, string toDate);
        InpatientStay getStayMovements(string checkinId);
        Site[] getSiteDivisions(string siteId);
        PatientCareTeam getPatientCareTeamMembers(string station);
    }
}
