using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;
using System.Collections;
using gov.va.medora.mdo.domain.ccr;

//NhinTypes = accession,allergy,appointment,document,immunization,lab,med,panel,patient,problem,procedure,radiology,rx,surgery,visit,vital

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public class PatientMedicalRecordTO : AbstractTO
    {
        public PatientMedicalRecordTO()
        {
            initCollections();
        }

        public PatientMedicalRecordTO(IndexedHashtable ihs)
        {
            //initCollections();

            if (ihs == null || ihs.Count == 0)
            {
                return;
            }

            FaultArray = new TaggedFaultArray(ihs); // we need a way to report faults at the top level - this should provide that

            Patient = new TaggedPatientArrays(ihs);
            Immunizations = new TaggedImmunizationArrays(ihs);
            Vitals = new TaggedVitalSignArrays(ihs);
            ImagingExams = new TaggedImagingExamArrays(ihs);
            Problems = new TaggedProblemArrays(ihs);
            DischargeSummaries = new TaggedNoteArrays(ihs, "dischargeSummaries");
            Notes = new TaggedNoteArrays(ihs);
            Labs = new TaggedLabReportArrays(ihs);
            Allergies = new TaggedAllergyArrays(ihs);
            EKGs = new TaggedClinicalProcedureArrays(ihs);
        }

        private void initCollections()
        {
            //CCR = new ContinuityOfCareRecord() { Body = new ContinuityOfCareRecordBody() };
            //CCR.Body.Immunizations = new List<StructuredProductType>();
            FaultArray = new TaggedFaultArray();
            Patient = new TaggedPatientArrays();
            Meds = new TaggedMedicationArrays();
            Allergies = new TaggedAllergyArrays();
            Appointments = new TaggedAppointmentArrays();
            Notes = new TaggedNoteArrays();
            //ChemHemReports = new TaggedChemHemRptArrays();
            //MicroReports = new TaggedMicrobiologyRptArrays();
            Problems = new TaggedProblemArrays();
            //RadiologyReports = new TaggedRadiologyReportArrays();
            SurgeryReports = new TaggedSurgeryReportArrays();
            Vitals = new TaggedVitalSignArrays();
            Immunizations = new TaggedImmunizationArrays();
            ImagingExams = new TaggedImagingExamArrays();
            EKGs = new TaggedClinicalProcedureArrays();
        }

        //public ContinuityOfCareRecord CCR { get; set; }
        public TaggedLabReportArrays Labs { get; set; }

        public TaggedFaultArray FaultArray { get; set; }

        public TaggedPatientArrays Patient { get; set; }

        public TaggedMedicationArrays Meds { get; set; }

        public TaggedAllergyArrays Allergies { get; set; }

        public TaggedAppointmentArrays Appointments { get; set; }

        public TaggedNoteArrays Notes { get; set; }

        public TaggedNoteArrays DischargeSummaries { get; set; }

        //public TaggedChemHemRptArrays ChemHemReports { get; set; }

        //public TaggedMicrobiologyRptArrays MicroReports { get; set; }

        public TaggedProblemArrays Problems { get; set; }

        //public TaggedRadiologyReportArrays RadiologyReports { get; set; }

        public TaggedSurgeryReportArrays SurgeryReports { get; set; }

        public TaggedVitalSignArrays Vitals { get; set; }

        public TaggedImmunizationArrays Immunizations { get; set; }

        public TaggedImagingExamArrays ImagingExams { get; set; }

        public TaggedClinicalProcedureArrays EKGs { get; set; }
    }
}