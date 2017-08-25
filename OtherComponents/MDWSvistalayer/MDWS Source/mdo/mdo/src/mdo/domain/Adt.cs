using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Adt
    {
	    string id;
	    Patient patient;
        string checkInId;
        string checkOutId;
        string relatedPhysicalMovementId;
	    string transaction;
	    string movementType;
        DateTime timestamp;
        string diagnosis;
        HospitalLocation assignedLocation;
        User provider;
        User attending;
        string transferFacility;
        KeyValuePair<string, string> specialty;
        string patientTxId;
        string visitId;
        string patientMovementNumber;
        string nextPatientMovement;
        User enteredBy;
        string lengthOfStay;
        string passDays;
        string daysAbsent;
        KeyValuePair<string, string> asihAdmission;
        string asihTransfer;
        string asihSequence;
        string asihDays;
        DateTime absenceReturnDate;
        bool admittedForScCondition;
        bool scheduledAdmission;
        string admissionSource;
        string admittingCategory;
        KeyValuePair<string, string> admittingRegulation;
        KeyValuePair<string, string> admittingEligibility;
        KeyValuePair<string, string> masMovementType;
        string lodgingReason;
        string lodgingComments;
        string disposition;
        string eligibility;
        string preAdmitId;
	    User referring;
	    User consulting;
	    User admitting;
        KeyValuePair<string,string> service;
	    HospitalLocation priorLocation;
	    HospitalLocation temporaryLocation;
	    HospitalLocation pendingLocation;
	    string patientType;
        DateTime admitTimestamp;
        DateTime dischargeTimestamp;
	    string admitReason;
	    string transferReason;

	    public Adt() {}

        public string Id
        {
            get
            {
                return id;
            }
            set
            {
                id = value;
            }
        }

        public Patient Patient
        {
            get
            {
                return patient;
            }
            set
            {
                patient = value;
            }
        }

        public string CheckInId
        {
            get
            {
                return checkInId;
            }
            set
            {
                checkInId = value;
            }
        }

        public string CheckOutId
        {
            get
            {
                return checkOutId;
            }
            set
            {
                checkOutId = value;
            }
        }

        public string RelatedPhysicalMovementId
        {
            get
            {
                return relatedPhysicalMovementId;
            }
            set
            {
                relatedPhysicalMovementId = value;
            }
        }

        public string MovementType
        {
            get
            {
                return movementType;
            }
            set
            {
                movementType = value;
            }
        }

        public string TransferFacility
        {
            get
            {
                return transferFacility;
            }
            set
            {
                transferFacility = value;
            }
        }

        public string Transaction
        {
            get
            {
                return transaction;
            }
            set
            {
                transaction = value;
            }
        }

        public KeyValuePair<string, string> Specialty
        {
            get
            {
                return specialty;
            }
            set
            {
                specialty = value;
            }
        }

        public string PreAdmitId
        {
            get
            {
                return preAdmitId;
            }
            set
            {
                preAdmitId = value;
            }
        }

        public User Provider
        {
            get
            {
                return provider;
            }
            set
            {
                provider = value;
            }
        }

        public User Attending
        {
            get
            {
                return attending;
            }
            set
            {
                attending = value;
            }
        }

        public User Referring
        {
            get
            {
                return referring;
            }
            set
            {
                referring = value;
            }
        }

        public User Consulting
        {
            get
            {
                return consulting;
            }
            set
            {
                consulting = value;
            }
        }

        public User Admitting
        {
            get
            {
                return admitting;
            }
            set
            {
                admitting = value;
            }
        }

        public KeyValuePair<string,string> Service
        {
            get
            {
                return service;
            }
            set
            {
                service = value;
            }
        }

        public HospitalLocation AssignedLocation
        {
            get
            {
                return assignedLocation;
            }
            set
            {
                assignedLocation = value;
            }
        }

        public HospitalLocation PriorLocation
        {
            get
            {
                return priorLocation;
            }
            set
            {
                priorLocation = value;
            }
        }

        public HospitalLocation TemporaryLocation
        {
            get
            {
                return temporaryLocation;
            }
            set
            {
                temporaryLocation = value;
            }
        }

        public HospitalLocation PendingLocation
        {
            get
            {
                return pendingLocation;
            }
            set
            {
                pendingLocation = value;
            }
        }

        public string PatientType
        {
            get
            {
                return patientType;
            }
            set
            {
                patientType = value;
            }
        }

        public string VisitId
        {
            get
            {
                return visitId;
            }
            set
            {
                visitId = value;
            }
        }

        public DateTime Timestamp
        {
            get
            {
                return timestamp;
            }
            set
            {
                timestamp = value;
            }
        }

        public DateTime AdmitTimestamp
	    {
            get
            {
                return admitTimestamp;
            }
            set
            {
                admitTimestamp = value;
            }
	    }

        public DateTime DischargeTimestamp
	    {
            get
            {
                return dischargeTimestamp;
            }
            set
            {
                dischargeTimestamp = value;
            }
	    }

	    public string AdmitReason
	    {
            get
            {
		        return admitReason;
            }
            set
            {
                admitReason = value;
            }
	    }

        public string TransferReason
        {
            get
            {
                return transferReason;
            }
            set
            {
                transferReason = value;
            }
        }

        public string Diagnosis
        {
            get
            {
                return diagnosis;
            }
            set
            {
                diagnosis = value;
            }
        }

        public string PatientTxId
        {
            get
            {
                return patientTxId;
            }
            set
            {
                patientTxId = value;
            }
        }

        public string PatientMovementNumber
        {
            get
            {
                return patientMovementNumber;
            }
            set
            {
                patientMovementNumber = value;
            }
        }

        public string NextPatientMovement
        {
            get
            {
                return nextPatientMovement;
            }
            set
            {
                nextPatientMovement = value;
            }
        }

        public User EnteredBy
        {
            get
            {
                return enteredBy;
            }
            set
            {
                enteredBy = value;
            }
        }

        public string LengthOfStay
        {
            get
            {
                return lengthOfStay;
            }
            set
            {
                lengthOfStay = value;
            }
        }

        public string PassDays
        {
            get
            {
                return passDays;
            }
            set
            {
                passDays = value;
            }
        }

        public string DaysAbsent
        {
            get
            {
                return daysAbsent;
            }
            set
            {
                daysAbsent = value;
            }
        }

        public KeyValuePair<string,string> AsihAdmission
        {
            get
            {
                return asihAdmission;
            }
            set
            {
                asihAdmission = value;
            }
        }

        public string AsihTransfer
        {
            get
            {
                return asihTransfer;
            }
            set
            {
                asihTransfer = value;
            }
        }

        public string AsihSequence
        {
            get
            {
                return asihSequence;
            }
            set
            {
                asihSequence = value;
            }
        }

        public string AsihDays
        {
            get
            {
                return asihDays;
            }
            set
            {
                asihDays = value;
            }
        }

        public DateTime AbsenceReturnDate
        {
            get
            {
                return absenceReturnDate;
            }
            set
            {
                absenceReturnDate = value;
            }
        }

        public bool AdmittedForScCondition
        {
            get
            {
                return admittedForScCondition;
            }
            set
            {
                admittedForScCondition = value;
            }
        }

        public bool ScheduledAdmission
        {
            get
            {
                return scheduledAdmission;
            }
            set
            {
                scheduledAdmission = value;
            }
        }

        public string AdmissionSource
        {
            get
            {
                return admissionSource;
            }
            set
            {
                admissionSource = value;
            }
        }

        public string AdmittingCategory
        {
            get
            {
                return admittingCategory;
            }
            set
            {
                admittingCategory = value;
            }
        }

        public KeyValuePair<string,string> AdmittingRegulation
        {
            get
            {
                return admittingRegulation;
            }
            set
            {
                admittingRegulation = value;
            }
        }

        public KeyValuePair<string,string> AdmittingEligibility
        {
            get
            {
                return admittingEligibility;
            }
            set
            {
                admittingEligibility = value;
            }
        }

        public KeyValuePair<string,string> MasMovementType
        {
            get
            {
                return masMovementType;
            }
            set
            {
                masMovementType = value;
            }
        }

        public string LodgingReason
        {
            get
            {
                return lodgingReason;
            }
            set
            {
                lodgingReason = value;
            }
        }

        public string LodgingComments
        {
            get
            {
                return lodgingComments;
            }
            set
            {
                lodgingComments = value;
            }
        }

        public string Disposition
        {
            get
            {
                return disposition;
            }
            set
            {
                disposition = value;
            }
        }

        public string Eligibility
        {
            get
            {
                return eligibility;
            }
            set
            {
                eligibility = value;
            }
        }

    }
}
