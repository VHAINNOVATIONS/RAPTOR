using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.ccr
{
    public class CCRHelper
    {

        public StructuredProductType buildMedObject(string medName, string id, string pharmId, string orderId, string rxNum,
            string startDate, string stopDate, string issueDate, string fillDate, string expirationDate,
            string sig, string strength, string units, string form, string dose, string frequency, string route, 
            string refills, string fillsRemaining, string quantity, string authorName, string authorId,
            string status, string rxType)
        {
            StructuredProductType newMed = new StructuredProductType();
            // MED TIMES
            // start/stop time range
            newMed.DateTime = new List<DateTimeType>();
            DateTimeType timeRange = new DateTimeType();
            timeRange.DateTimeRange = new List<DateTimeTypeDateTimeRange>();
            DateTimeTypeDateTimeRange range = new DateTimeTypeDateTimeRange();
            range.BeginRange = new DateTimeTypeDateTimeRangeBeginRange();
            range.BeginRange.ExactDateTime = startDate;
            range.EndRange = new DateTimeTypeDateTimeRangeEndRange();
            range.EndRange.ExactDateTime = stopDate;
            timeRange.DateTimeRange.Add(range);
            newMed.DateTime.Add(timeRange);
            // prescribed time
            DateTimeType created = new DateTimeType();
            created.ExactDateTime = issueDate;
            created.Type = new CodedDescriptionType();
            created.Type.Text = "Prescription date";
            newMed.DateTime.Add(created);
            // last fill time
            DateTimeType filled = new DateTimeType();
            filled.ExactDateTime = fillDate;
            filled.Type = new CodedDescriptionType();
            filled.Type.Text = "Dispense date";
            newMed.DateTime.Add(filled);
            // expiration date
            DateTimeType expires = new DateTimeType();
            expires.ExactDateTime = expirationDate;
            expires.Type = new CodedDescriptionType();
            expires.Type.Text = "Expiration date";
            newMed.DateTime.Add(expires);
            // END MED TIMES
            // MED NAME AND DETAILS
            // med name and info
            newMed.Product = new List<StructuredProductTypeProduct>();
            StructuredProductTypeProduct medProduct = new StructuredProductTypeProduct();
            medProduct.ProductName = new CodedDescriptionType();
            medProduct.ProductName.Text = medName;
            medProduct.Strength = new List<StructuredProductTypeProductStrength>();
            StructuredProductTypeProductStrength medStrength = new StructuredProductTypeProductStrength();
            medStrength.Units = new MeasureTypeUnits();
            medStrength.Units.Unit = units;
            medStrength.Value = strength;
            medProduct.Strength.Add(medStrength);
            medProduct.Form = new List<StructuredProductTypeProductForm>();
            medProduct.Form.Add(new StructuredProductTypeProductForm() { Text = form });

            newMed.Product.Add(medProduct);
            // END MED NAME AND DETAILS

            // MED DIRECTIONS
            newMed.Directions = new List<Direction>();
            Direction direction = new Direction();
            direction.Route = new List<DirectionRoute>();
            direction.Route.Add(new DirectionRoute() { Text = route });
            direction.Dose = new List<DirectionDose>();
            direction.Dose.Add(new DirectionDose() { Value = dose });
            direction.Frequency = new List<FrequencyType>();
            direction.Frequency.Add(new FrequencyType() { Value = frequency });
            newMed.Directions.Add(direction);

            newMed.PatientInstructions = new List<InstructionType>();
            newMed.PatientInstructions.Add(new InstructionType() { Text = sig });
            // END MED DIRECTIONS

            // PROVIDER INFO
            newMed.Source = new List<SourceType>();
            //ActorType provider = new ActorType();
            //provider.ActorObjectID = authorName;
            //provider.IDs = new List<IDType>();
            //provider.IDs.Add(new IDType() { ID = authorId });
            SourceType source = new SourceType()
            {
                Actor = new List<ActorReferenceType>() 
                { 
                    new ActorReferenceType() 
                    { 
                        ActorID = authorName, 
                        ActorRole = new List<CodedDescriptionType>()
                        {
                            new CodedDescriptionType() { Text = "Prescribing clinician" } 
                        } 
                    }
                }
            };
            newMed.Source.Add(source);
            // END PROVIDER INFO

            // MED IDs
            medProduct.IDs = new List<IDType>();

            IDType medId = new IDType();
            medId.ID = id;
            medId.Type = new CodedDescriptionType();
            medId.Type.Text = "ID";
            medProduct.IDs.Add(medId);

            IDType pharmacyId = new IDType();
            pharmacyId.ID = pharmId;
            pharmacyId.Type = new CodedDescriptionType();
            pharmacyId.Type.Text = "Pharmacy ID";
            medProduct.IDs.Add(pharmacyId);

            IDType medOrderId = new IDType();
            medOrderId.ID = orderId;
            medOrderId.Type = new CodedDescriptionType();
            medOrderId.Type.Text = "Order ID";
            medProduct.IDs.Add(medOrderId);

            IDType medRxNum = new IDType();
            medRxNum.ID = rxNum;
            medRxNum.Type = new CodedDescriptionType();
            medRxNum.Type.Text = "Rx Number";
            medProduct.IDs.Add(medRxNum);
            // END MED IDs

            newMed.Status = new CodedDescriptionType();
            newMed.Status.Text = status;
            newMed.Type = new CodedDescriptionType() { Text = rxType };
            newMed.Quantity = new List<QuantityType>();
            QuantityType medQuantity = new QuantityType() { Value = quantity };
            newMed.Quantity.Add(medQuantity);
            newMed.Refills = new List<StructuredProductTypeRefill>();
            StructuredProductTypeRefill medRefills = new StructuredProductTypeRefill() { Number = new List<string>() { fillsRemaining } };
            medRefills.Quantity = new List<QuantityType>() { new QuantityType() { Value = refills } };
            newMed.Refills.Add(medRefills);

            return newMed;
        }

        public ProblemType buildProblemObject(string problemName, string problemId,
            string startDate, string stopDate, string entered, string lastUpdated, 
            string providerName, 
            string codingSystem, string codingValue, string codingVersion,
            string statusCode, string statusText)
        {
            ProblemType problem = new ProblemType();

            problem.DateTime = new List<DateTimeType>();
            problem.DateTime.Add(new DateTimeType() { ExactDateTime = startDate, Type = new CodedDescriptionType() { Text = "Start date" } });
            problem.DateTime.Add(new DateTimeType() { ExactDateTime = stopDate, Type = new CodedDescriptionType() { Text = "Stop date" } });
            problem.DateTime.Add(new DateTimeType() { ExactDateTime = entered, Type = new CodedDescriptionType() { Text = "Entered date" } });
            problem.DateTime.Add(new DateTimeType() { ExactDateTime = lastUpdated, Type = new CodedDescriptionType() { Text = "Updated date" } });

            problem.Source = new List<SourceType>();
            problem.Source.Add(new SourceType() { Actor = new List<ActorReferenceType>() });
            problem.Source[0].Actor.Add(new ActorReferenceType() { ActorID = providerName });
            problem.Source[0].Actor[0].ActorRole = new List<CodedDescriptionType>();
            problem.Source[0].Actor[0].ActorRole.Add(new CodedDescriptionType() { Text = "Treating clinician" });

            problem.Description = new CodedDescriptionType();
            problem.Description.Text = problemName;
            problem.Description.Code = new List<CodeType>();
            problem.Description.Code.Add(new CodeType() { CodingSystem = codingSystem, Value = codingValue, Version = codingVersion });

            problem.Status = new CodedDescriptionType();
            problem.Status.Code = new List<CodeType>() { new CodeType() { Value = statusCode } };
            problem.Status.Text = statusText;
            
            problem.IDs = new List<IDType>() { new IDType() { ID = problemId } };

            return problem;
        }

        public TestType buildLabObject(string id, string accessionNumber, 
            string testType, string specimen, string status,
            string collectedDate, string completedDate,
            string result, string units, string codingValue, string codingSystem, 
            string normalLow, string normalHigh)
        {
            TestType test = new TestType();
            test.NormalResult = new List<NormalType>();
            test.NormalResult.Add(new NormalType() { Value = normalLow + " - " + normalHigh });

            test.Description = new CodedDescriptionType() { Text = testType + " - " + specimen };
            test.Description.Code = new List<CodeType>();
            test.Description.Code.Add(new CodeType() { Value = codingValue, CodingSystem = codingSystem });

            test.Status = new CodedDescriptionType() { Text = status };

            DateTimeType collected = new DateTimeType() { ExactDateTime = collectedDate, Type = new CodedDescriptionType() { Text = "Collection date" } };
            DateTimeType completed = new DateTimeType() { ExactDateTime = completedDate, Type = new CodedDescriptionType() { Text = "Completed date" } };
            test.DateTime = new List<DateTimeType>() { collected, completed };

            IDType testId = new IDType() { ID = id, Type = new CodedDescriptionType() { Text = "ID" } };
            IDType accessionId = new IDType() { ID = accessionNumber, Type = new CodedDescriptionType() { Text = "Accession number" } };
            test.IDs = new List<IDType>() { testId, accessionId };

            TestResultType testResult = new TestResultType();
            testResult.Units = new RateTypeUnits();
            testResult.Units.Unit = units;
            testResult.Value = result;
            test.TestResult = testResult;

            return test;
        }

        public AlertType buildAllergyObject(string id, string localId, string name, string allergyType, string allergyTypeCode,
            string enteredDate, string verifiedDate, string status,
            IList<string> reactions)
        {
            AlertType allergy = new AlertType();

            allergy.Description = new CodedDescriptionType() { Text = name };

            DateTimeType entered = new DateTimeType() { ExactDateTime = enteredDate, Type = new CodedDescriptionType() { Text = "Entered date" } };
            DateTimeType verified = new DateTimeType() { ExactDateTime = verifiedDate, Type = new CodedDescriptionType() { Text = "Verified date" } };
            allergy.DateTime = new List<DateTimeType>() { entered, verified };

            IDType id1 = new IDType() { ID = id, Type = new CodedDescriptionType() { Text = "ID" } };
            IDType id2 = new IDType() { ID = localId, Type = new CodedDescriptionType() { Text = "Local ID" } };
            allergy.IDs = new List<IDType>() { id1, id2 };

            allergy.Type = new CodedDescriptionType() { Text = allergyType, Code = new List<CodeType>() { new CodeType() { Value = allergyTypeCode } } };
            allergy.Status = new CodedDescriptionType() { Text = status };

            allergy.Reaction = new List<Reaction>();
            if (reactions != null && reactions.Count > 0)
            {
                foreach (string reaction in reactions)
                {
                    allergy.Reaction.Add(new Reaction() { Description = new CodedDescriptionType() { Text = reaction } });
                }
            }
            //allergy.Agent = new List<Agent>();
            //Agent agent = new Agent();
            //agent.Products = new List<StructuredProductType>();
            //StructuredProductType t1 = new StructuredProductType();

            return allergy;
        }

        public ActorType buildPatientObject(string id, string ssn, string firstname, string lastName, string middleName,
            string dob, string age, string gender, DemographicSet demogs)
        {
            ActorType patient = new ActorType();
            ActorTypePerson person = new ActorTypePerson() 
            { 
                Name = new ActorTypePersonName() 
                { 
                    CurrentName = new PersonNameType() 
                    {  
                        Family = new List<string>() { lastName },
                        Given = new List<string>() { firstname },
                        Middle = new List<string>() { middleName }
                    }
                },
                Gender = new CodedDescriptionType() { Text = gender } 
            };
            person.DateOfBirth = new DateTimeType() 
            { 
                ExactDateTime = dob, 
                Age = new MeasureType() { Value = age } 
            };
            patient.Item = person;

            patient.Address = new List<ActorTypeAddress>();
            if (demogs != null && demogs.StreetAddresses != null && demogs.StreetAddresses.Count > 0)
            {
                foreach (Address addr in demogs.StreetAddresses)
                {
                    ActorTypeAddress newAddr = new ActorTypeAddress()
                    {
                        City = addr.City,
                        County = addr.County,
                        Line1 = addr.Street1,
                        Line2 = addr.Street2,
                        PostalCode = addr.Zipcode,
                        State = addr.State
                    };
                    patient.Address.Add(newAddr);
                }
            }

            patient.EMail = new List<CommunicationType>();
            if (demogs != null && demogs.EmailAddresses != null && demogs.EmailAddresses.Count > 0)
            {
                foreach (EmailAddress addy in demogs.EmailAddresses)
                {
                    CommunicationType newEmail = new CommunicationType()
                    {
                        Type = new CodedDescriptionType() { Text = "Email" },
                        Value = addy.Address
                    };
                    patient.EMail.Add(newEmail);
                }
            }

            patient.Telephone = new List<CommunicationType>();
            if (demogs != null && demogs.PhoneNumbers != null && demogs.PhoneNumbers.Count > 0)
            {
                foreach (PhoneNum phone in demogs.PhoneNumbers)
                {
                    CommunicationType newPhone = new CommunicationType()
                    {
                        Value = phone.ToString()
                    };
                    if (String.IsNullOrEmpty(phone.Description))
                    {
                        newPhone.Type = new CodedDescriptionType() { Text = "Telephone" };
                    }
                    else
                    {
                        newPhone.Type = new CodedDescriptionType() { Text = phone.Description };
                    }
                    patient.Telephone.Add(newPhone);
                }
            }

            IDType patientId = new IDType() { ID = id, Type = new CodedDescriptionType() { Text = "ID" } };
            IDType patientSSN = new IDType() { ID = ssn, Type = new CodedDescriptionType() { Text = "SSN" } };
            patient.IDs = new List<IDType>() { patientId, patientSSN };


            return patient;
        }

        internal StructuredProductType buildImmunizationObject(string immIen, string timestamp, string contraindicated, string encounter, string facility, string name, string reaction)
        {
            StructuredProductType immunization = new StructuredProductType();

            immunization.IDs = new List<IDType>() { new IDType() { ID = immIen, Type = new CodedDescriptionType() { Text = "ID" } } };
            immunization.DateTime = new List<DateTimeType>() { new DateTimeType() { ExactDateTime = timestamp, Type = new CodedDescriptionType() { Text = "Administered" } } };
            immunization.Product = new List<StructuredProductTypeProduct>() { new StructuredProductTypeProduct() { ProductName = new CodedDescriptionType() { Text = name } } };
            immunization.Reaction = new Reaction() { Description = new CodedDescriptionType() { Text = reaction } };
            
            return immunization;
        }
    }
}
