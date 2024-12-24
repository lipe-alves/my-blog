SELECT p.*, (
                    SELECT 
                        GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') 
                    FROM 
                        Post_x_Category pc
                        JOIN Category c ON c.id = pc.category_id
                    WHERE
                        pc.post_id = p.id
                ) AS category_names FROM Post p LEFT JOIN Post_x_Category pc ON pc.post_id = p.id LEFT JOIN Category c ON c.id = pc.category_id WHERE 1 = 1 AND c.name = :category_name GROUP BY p.id LIMIT :offset, :limit