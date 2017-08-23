using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class AdtTO : AbstractTO
    {
        public String id;
        public PatientTO patient;
        public String checkInId;
        public String checkOutId;
        public String relatedPhysicalMovementId;
        public String transaction;
        public String movementType;
        public String timestamp;
        public String diagnosis;
        public HospitalLocationTO assignedLocation;
        public UserTO provider;
        public UserTO attending;
        public String transferFacility;
        public TaggedText specialty;
        public String patientTxId;
        public String visitId;
        public String patientMovementNumber;
        public String nextPatientMovement;
        public UserTO enteredBy;
        public String lengthOfStay;
        public String passDays;
        public String daysAbsent;
        public TaggedText asihAdmission;
        public String asihTransfer;
        public String asihSequence;
        public String asihDays;
        public String absenceReturnDate;
        public bool admittedForScCondition;
        public bool scheduledAdmission;
        public String admissionSource;
        public String admittingCategory;
        public TaggedText admittingRegulation;
        public TaggedText admittingEligibility;
        public TaggedText masMovementType;
        public String lodgingReason;
        public String lodgingComments;
        public String disposition;
        public String eligibility;
        public String preAdmitId;
        public UserTO referring;
        public UserTO consulting;
        public UserTO admitting;
        public TaggedText service;
        public HospitalLocationTO priorLocation;
        public HospitalLocationTO temporaryLocation;
        public HospitalLocationTO pendingLocation;
        public String patientType;
        public String admitTimestamp;
        public String dischargeTimestamp;
        public String admitReason;
        public String transferReason;

        public AdtTO() { }

        public AdtTO(Adt mdo)
        {
            if (mdo == null)
            {
                return;
            }
            this.id = mdo.Id;
            this.patient = new PatientTO(mdo.Patient);
            this.checkInId = mdo.CheckInId;
            this.checkOutId = mdo.CheckOutId;
            this.relatedPhysicalMovementId = mdo.RelatedPhysicalMovementId;
            this.transaction = mdo.Transaction;
            this.movementType = mdo.MovementType;
            this.timestamp = mdo.Timestamp.ToString("yyyyMMdd.HHmmss");
            this.diagnosis = mdo.Diagnosis;
            this.assignedLocation = new HospitalLocationTO(mdo.AssignedLocation);
            this.provider = new UserTO(mdo.Provider);
            this.attending = new UserTO(mdo.Attending);
            this.transferFacility = mdo.TransferFacility;
            this.specialty = new TaggedText(mdo.Specialty);
            this.patientTxId = mdo.PatientTxId;
            this.visitId = mdo.VisitId;
            this.patientMovementNumber = mdo.PatientMovementNumber;
            this.nextPatientMovement = mdo.NextPatientMovement;
            this.enteredBy = new UserTO(mdo.EnteredBy);
            this.lengthOfStay = mdo.LengthOfStay;
            this.passDays = mdo.PassDays;
            this.daysAbsent = mdo.DaysAbsent;
            this.asihAdmission = new TaggedText(mdo.AsihAdmission);
            this.asihTransfer = mdo.AsihTransfer;
            this.asihSequence = mdo.AsihSequence;
            this.asihDays = mdo.AsihDays;
            this.absenceReturnDate = mdo.AbsenceReturnDate.ToString("yyyyMMdd.HHmmss");
            this.admittedForScCondition = mdo.AdmittedForScCondition;
            this.scheduledAdmission = mdo.ScheduledAdmission;
            this.admissionSource = mdo.AdmissionSource;
            this.admittingCategory = mdo.AdmittingCategory;
            this.admittingRegulation = new TaggedText(mdo.AdmittingRegulation);
            this.admittingEligibility = new TaggedText(mdo.AdmittingEligibility);
            this.masMovementType = new TaggedText(mdo.MasMovementType);
            this.lodgingReason = mdo.LodgingReason;
            this.lodgingComments = mdo.LodgingComments;
            this.disposition = mdo.Disposition;
            this.eligibility = mdo.Eligibility;
            this.preAdmitId = mdo.PreAdmitId;
            this.referring = new UserTO(mdo.Referring);
            this.consulting = new UserTO(mdo.Consulting);
            this.admitting = new UserTO(mdo.Admitting);
            this.service = new TaggedText(mdo.Service);
            this.priorLocation = new HospitalLocationTO(mdo.PriorLocation);
            this.temporaryLocation = new HospitalLocationTO(mdo.TemporaryLocation);
            this.pendingLocation = new HospitalLocationTO(mdo.PendingLocation);
            this.patientType = mdo.PatientType;
            this.admitTimestamp = mdo.AdmitTimestamp.ToString("yyyyMMdd.HHmmss");
            this.dischargeTimestamp = mdo.DischargeTimestamp.ToString("yyyyMMdd.HHmmss");
            this.admitReason = mdo.AdmitReason;
            this.transferReason = mdo.TransferReason;
        }

    }
}
