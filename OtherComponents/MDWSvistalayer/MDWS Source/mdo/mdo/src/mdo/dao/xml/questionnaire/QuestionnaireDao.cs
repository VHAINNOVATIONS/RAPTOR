using System;
using System.Collections.Generic;
using System.Xml;
using System.IO;

namespace gov.va.medora.mdo.dao.xml.questionnaire
{
    public class QuestionnaireDao
    {
        public QuestionnaireDao() { }

        public QuestionnaireSet getSet(string filepath)
        {
            if (String.IsNullOrEmpty(filepath))
            {
                throw new ArgumentNullException("filename");
            }
            if (!File.Exists(filepath))
            {
                throw new ArgumentException("Missing XML questionnaire definition file");
            }

            QuestionnaireSet qset = null;
            List<QuestionnaireQuestion> currentQuestions = null;
            Questionnaire currentQuestionnaire = null;
            QuestionnaireQuestion currentQuestion = null;
            QuestionnaireSection currentSection = null;
            QuestionnairePage currentPage = null;
            QuestionnaireSubsection currentSubsection = null;

            XmlReader reader = new XmlTextReader(filepath);
            while (reader.Read())
            {
                switch ((int)reader.NodeType)
                {
                    case (int)XmlNodeType.Element:
                        string name = reader.Name;
                        if (name == "QuestionnaireSet")
                        {
                            qset = new QuestionnaireSet();
                            qset.Name = reader.GetAttribute("name");
                            qset.Title = reader.GetAttribute("title");
                            qset.Description = reader.GetAttribute("description");
                            qset.Questionnaires = new List<Questionnaire>();
                        }
                        else if (name == "Introduction")
                        {
                            currentQuestions = new List<QuestionnaireQuestion>();
                        }
                        else if (name == "Questionnaire")
                        {
                            currentQuestionnaire = new Questionnaire();
                            currentQuestionnaire.Name = reader.GetAttribute("name");
                            currentQuestionnaire.Title = reader.GetAttribute("title");
                            currentQuestionnaire.Description = reader.GetAttribute("description");
                            currentQuestionnaire.Sections = new List<QuestionnaireSection>();
                        }
                        else if (name == "Choices")
                        {
                            currentQuestion.Choices = new List<KeyValuePair<string, string>>();
                        }
                        else if (name == "Choice")
                        {
                            string nbr = reader.GetAttribute("number");
                            string txt = reader.GetAttribute("text");
                            currentQuestion.Choices.Add(new KeyValuePair<string, string>(nbr, txt));
                        }
                        else if (name == "Section")
                        {
                            currentSection = new QuestionnaireSection();
                            currentSection.Name = reader.GetAttribute("name");
                            currentSection.Title = reader.GetAttribute("title");
                            currentSection.Pages = new List<QuestionnairePage>();
                        }
                        else if (name == "Page")
                        {
                            currentPage = new QuestionnairePage();
                            currentPage.Number = reader.GetAttribute("number");
                            currentPage.Sections = new List<QuestionnaireSubsection>();
                        }
                        else if (name == "Subsection")
                        {
                            currentSubsection = new QuestionnaireSubsection();
                            currentSubsection.Name = reader.GetAttribute("name");
                            currentSubsection.Title = reader.GetAttribute("title");
                        }
                        else if (name == "Questions")
                        {
                            currentQuestions = new List<QuestionnaireQuestion>();
                        }
                        else if (name == "Question")
                        {
                            currentQuestion = new QuestionnaireQuestion();
                            currentQuestion.Number = reader.GetAttribute("number");
                            currentQuestion.Text = reader.GetAttribute("text");
                            currentQuestion.BranchFromQuestion = reader.GetAttribute("branchFrom");
                            currentQuestion.BranchCondition = reader.GetAttribute("if");
                            currentQuestions.Add(currentQuestion);
                        }
                        break;
                    case (int)XmlNodeType.EndElement:
                        name = reader.Name;
                        if (name == "Introduction")
                        {
                            qset.Questions = currentQuestions;
                            currentQuestions = null;
                        }
                        else if (name == "Questionnaire")
                        {
                            qset.Questionnaires.Add(currentQuestionnaire);
                            currentQuestionnaire = null;
                        }
                        else if (name == "Choices")
                        {
                        }
                        else if (name == "Section")
                        {
                            currentQuestionnaire.Sections.Add(currentSection);
                            currentSection = null;
                        }
                        else if (name == "Page")
                        {
                            currentSection.Pages.Add(currentPage);
                            currentPage = null;
                        }
                        else if (name == "Subsection")
                        {
                            currentPage.Sections.Add(currentSubsection);
                            currentSubsection = null;
                        }
                        else if (name == "Questions")
                        {
                            currentSubsection.Questions = currentQuestions;
                            currentQuestions = null;
                        }
                        break;
                }
            }
            return qset;
        }
    }
}
