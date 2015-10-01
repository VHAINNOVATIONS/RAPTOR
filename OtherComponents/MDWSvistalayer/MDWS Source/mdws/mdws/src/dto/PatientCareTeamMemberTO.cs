using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class PatientCareTeamMemberTO : AbstractTO
    {

        private string teamName;
        private string teamStartDate;
        private string teamEndDate;
        private string currentProviderFlag;
        private string associateProviderFlag;
        private string teamPurpose;
        private string providerRole;
        private string primaryPosition;
        private string primaryStandardPosition;
        private string associatePosition;
        private string associateStandardPosition;
        private PersonTO person;

        public PatientCareTeamMemberTO() { }

        public PatientCareTeamMemberTO(PatientCareTeamMember member)
        {
            teamName = member.TeamName;
            teamStartDate = member.TeamStartDate;
            teamEndDate = member.TeamEndDate;
            currentProviderFlag = member.CurrentProviderFlag;
            associateProviderFlag = member.AssociateProviderFlag;
            teamPurpose = member.TeamPurpose;
            providerRole = member.ProviderRole;
            primaryPosition = member.PrimaryPosition;
            primaryStandardPosition = member.PrimaryStandardPosition;
            associatePosition = member.AssociatePosition;
            associateStandardPosition = member.AssociateStandardPosition;
            person = new PersonTO(member.Person);
        }


        public string TeamName
        {
            get { return teamName; }
            set { teamName = value; }
        }

        public string TeamStartDate
        {
            get { return teamStartDate; }
            set { teamStartDate = value; }
        }

        public string TeamEndDate
        {
            get { return teamEndDate; }
            set { teamEndDate = value; }
        }

        public string CurrentProviderFlag
        {
            get { return currentProviderFlag; }
            set { currentProviderFlag = value; }
        }

        public string AssociateProviderFlag
        {
            get { return associateProviderFlag; }
            set { associateProviderFlag = value; }
        }

        public string TeamPurpose
        {
            get { return teamPurpose; }
            set { teamPurpose = value; }
        }

        public string ProviderRole
        {
            get { return providerRole; }
            set { providerRole = value; }
        }

        public string PrimaryPosition
        {
            get { return primaryPosition; }
            set { primaryPosition = value; }
        }

        public string PrimaryStandardPosition
        {
            get { return primaryStandardPosition; }
            set { primaryStandardPosition = value; }
        }

        public string AssociatePosition
        {
            get { return associatePosition; }
            set { associatePosition = value; }
        }

        public string AssociateStandardPosition
        {
            get { return associateStandardPosition; }
            set { associateStandardPosition = value; }
        }

        public PersonTO Person
        {
            get { return person; }
            set { person = value; }
        }


    }
}