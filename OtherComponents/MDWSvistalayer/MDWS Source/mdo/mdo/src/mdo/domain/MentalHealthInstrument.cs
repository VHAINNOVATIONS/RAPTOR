using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo
{
    public class MentalHealthInstrument
    {
        string id;
        string name;
        string title;
        string author;
        string publisher;
        string publicationDate;
        string reference;
        string a_privilege;
        string r_privilege;
        string operationalStatus;
        bool hasBeenOperational;
        bool requiresLicense;
        string purpose;
        string normSample;
        string targetPopulation;
        string enteredBy;
        string entryDate;
        string lastEditedBy;
        string lastEditDate;
        bool isNationalTest;
        bool isLicenseCurrent;
        string copyrightText;
        bool requiresSignature;
        bool isLegacy;
        bool submitToNationalDb;
        bool isCopyrighted;
        bool writeFullText;
        string daysToRestart;

        public MentalHealthInstrument() { }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        public string Title
        {
            get { return title; }
            set { title = value; }
        }

        public string Author
        {
            get { return author; }
            set { author = value; }
        }

        public string Publisher
        {
            get { return publisher; }
            set { publisher = value; }
        }

        public string PublicationDate
        {
            get { return publicationDate; }
            set { publicationDate = value; }
        }

        public string Reference
        {
            get { return reference; }
            set { reference = value; }
        }

        public string A_Privilege
        {
            get { return a_privilege; }
            set { a_privilege = value; }
        }

        public string R_Privilege
        {
            get { return r_privilege; }
            set { r_privilege = value; }
        }

        public string OperationalStatus
        {
            get { return operationalStatus; }
            set { operationalStatus = value; }
        }

        public bool HasBeenOperational
        {
            get { return hasBeenOperational; }
            set { hasBeenOperational = value; }
        }

        public bool RequiresLicense
        {
            get { return requiresLicense; }
            set { requiresLicense = value; }
        }

        public string Purpose
        {
            get { return purpose; }
            set { purpose = value; }
        }

        public string NormativeSample
        {
            get { return normSample; }
            set { normSample = value; }
        }

        public string TargetPopulation
        {
            get { return targetPopulation; }
            set { targetPopulation = value; }
        }

        public string EnteredBy
        {
            get { return enteredBy; }
            set { enteredBy = value; }
        }

        public string EntryDate
        {
            get { return entryDate; }
            set { entryDate = value; }
        }

        public string LastEditedBy
        {
            get { return lastEditedBy; }
            set { lastEditedBy = value; }
        }

        public string LastEditDate
        {
            get { return lastEditDate; }
            set { lastEditDate = value; }
        }

        public bool IsNationalTest
        {
            get { return isNationalTest; }
            set { isNationalTest = value; }
        }

        public bool IsLicenseCurrent
        {
            get { return isLicenseCurrent; }
            set { isLicenseCurrent = value; }
        }

        public string CopyrightText
        {
            get { return copyrightText; }
            set { copyrightText = value; }
        }

        public bool RequiresSignature
        {
            get { return requiresSignature; }
            set { requiresSignature = value; }
        }

        public bool IsLegacy
        {
            get { return isLegacy; }
            set { isLegacy = value; }
        }

        public bool SubmitToNationalDb
        {
            get { return submitToNationalDb; }
            set { submitToNationalDb = value; }
        }

        public bool IsCopyrighted
        {
            get { return isCopyrighted; }
            set { isCopyrighted = value; }
        }

        public bool WriteFullText
        {
            get { return writeFullText; }
            set { writeFullText = value; }
        }

        public string DaysToRestart
        {
            get { return daysToRestart; }
            set { daysToRestart = value; }
        }
    }
}
